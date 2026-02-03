<?php
/**
 * Contact Form for BD4D
 *
 * @package BD4D
 * @since   1.0.0
 */

/**
 * Holds methods for Contact Form
 * Class BD4D
 */
class BD4D {

	const BASE_URL = 'https://api.airtable.com/v0';

	const SEND_SUCCESS      = 1;
	const SEND_ERROR        = 3;
	const JSON_ERROR        = 4;
	const NO_CONTACT_FOUND  = 5;
	const FORM_INCOMPLETE   = 6;
	const RECAPTCHA_MISSING = 7;
	const RECAPTCHA_FAILED  = 8;
	const NONCE_FAILED      = 9;
	const EMAIL_MISSING     = 10;


	const FIELD_NAME = 'newsletter_form';
	const NONCE_KEY  = 'newsletter_form_nonce';

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_shortcode( 'bd4d-contact-form', [ __CLASS__, 'render_email_form' ] );
		add_action( 'wp_ajax_nopriv_send_message', [ __CLASS__, 'send_message' ] );
		add_action( 'wp_ajax_send_message', [ __CLASS__, 'send_message' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public static function enqueue_scripts() {
		$ext = defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ? 'src' : 'min';

		wp_enqueue_style( 'bd4d', plugins_url( "assets/css/main.{$ext}.css", __DIR__ ), [], BD4D_VERSION );
		wp_enqueue_script( 'bd4d', plugins_url( "assets/js/main.{$ext}.js", __DIR__ ), [], BD4D_VERSION, false );
			
		wp_localize_script(
			'bd4d',
			'localize',
			[
				'_ajax_url'   => admin_url( 'admin-ajax.php' ),
				'_ajax_nonce' => wp_create_nonce( self::FIELD_NAME ),
				'sitekey'     => Google_Recaptcha::get_site_key(),
				'error_codes' => [
					self::SEND_ERROR        => 'Unable to send message',
					self::JSON_ERROR        => 'Unable to parse JSON result.',
					self::RECAPTCHA_MISSING => 'The ReCATCHA token was missing.',
					self::FORM_INCOMPLETE   => 'Enter an email address or a message.',
					self::RECAPTCHA_FAILED  => 'ReCAPTCHA could not validate you are a human.',
					self::NONCE_FAILED      => 'WordPress could not validate you are a human.',
					self::EMAIL_MISSING     => 'Enter an email address.',
				],
			]
		);
	}

	/**
	 * Generate headers needed by Airtable API.
	 *
	 * @throws Exception If no API token found.
	 * @return array     Array of Authorization and Content-Type headers.
	 */
	private static function headers() {
		$token = get_option( 'bd4d_airtable_token' );
		if ( ! $token ) {
			throw new Exception( 'Missing Airtable API token.' );
		}
		return [
			'Authorization' => "Bearer {$token}",
			'Content-Type'  => 'application/json',
		];
	}

	/**
	 * Get Airtable base id for this newsletter.
	 *
	 * @throws Exception If no API token found.
	 * @return int       Airtable base identifier.
	 */
	private static function base_id() {
		$base_id = get_option( 'bd4d_airtable_base_id' );
		if ( ! $base_id ) {
			throw new Exception( 'Missing Airable base ID.' );
		}

		return $base_id;
	}

	/**
	 * Get Airtable table id for this newsletter.
	 *
	 * @throws Exception If no API token found.
	 * @return int       Airtable table identifier.
	 */
	private static function table_id() {
		$list_id = get_option( 'bd4d_airtable_table_id' );
		if ( ! $list_id ) {
			throw new Exception( 'Missing Airable table ID.' );
		}

		return $list_id;
	}

	/**
	 * Determine if all needed configuration values are set
	 *
	 * @param array $errors  Array of unconfigured values.
	 * @return boolean       If needed configuration values are set.
	 */
	public static function is_configured( &$errors ) {
		$items = [
			'Airtable Token'       => 'bd4d_airtable_token',
			'Airtable Table ID'    => 'bd4d_airtable_table_id',
			'Airtable Base ID'     => 'bd4d_airtable_base_id',
			'ReCAPTCHA Site Key'   => 'bd4d_recaptcha_site_key',
			'ReCAPTCHA Secret Key' => 'bd4d_recaptcha_secret_key',
		];
		foreach ( $items as $label => $key ) {
			$value = get_option( $key );
			if ( ! $value ) {
				$errors[] = $label;
			}
		}

		return count( $errors ) === 0;
	}


	/**
	 * Add the user to a Airtable table.
	 *
	 * @param string  $email              User's email address.
	 * @param string  $first_name         User's first name.
	 * @param string  $last_name          User's last name.
	 * @param string  $affiliation        User's company or organization.
	 * @param string  $message            User's message.
	 * @param boolean $newsletter         Whether or not to subscribe to the newsletter.
	 * @param boolean $supporter          Whether or not to identify the user as a supporter.
	 * @param boolean $adoption           Whether or not the user wants to learn about adopting BD4D.
	 */
	public static function add( $email, $first_name = false, $last_name = false, $affiliation = false, $message = false, $newsletter = false, $supporter = false, $adoption = false ) {
		$data = [ 'fields' => [ 'Email Address' => $email ] ];
		if ( $first_name ) {
			$data['fields']['First Name'] = $first_name;
		}
		if ( $last_name ) {
			$data['fields']['Last Name'] = $last_name;
		}
		if ( $affiliation ) {
			$data['fields']['Affiliation'] = $affiliation;
		}
		if ( $message ) {
			$data['fields']['Form Comments'] = $message;
		}
		
		$data['fields']['Email-Opted In?'] = $newsletter;
		$data['fields']['CotW-Opted In?']  = $supporter;
		$data['fields']['Adoption?']       = $adoption;

		$start_time   = microtime( true );
		$raw_result   = wp_remote_post(
			self::BASE_URL . '/' . self::base_id() . '/' . self::table_id(),
			[
				'headers' => self::headers(),
				'body'    => wp_json_encode( $data ),
				'timeout' => 15, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- 15s needed for slow Airtable responses.
			]
		);
		$elapsed_time = round( ( microtime( true ) - $start_time ) * 1000 );

		if ( is_wp_error( $raw_result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of API errors for debugging.
			error_log( 'BD4D contact form Airtable API error: ' . $raw_result->get_error_message() . ' (took ' . $elapsed_time . 'ms)' );
			return self::SEND_ERROR;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of API timing for debugging.
		error_log( 'BD4D contact form Airtable API success (took ' . $elapsed_time . 'ms)' );

		$result        = json_decode( $raw_result['body'], true );
		$http_response = $raw_result['response'];

		if ( ! $result ) {
			return self::JSON_ERROR;
		}
		if ( isset( $http_response['code'] ) && 200 === $http_response['code'] ) {
			$id = $result['id'];
			if ( $id ) {
				if ( $result['fields']['First Name'] === $first_name &&
				$result['fields']['Last Name'] === $last_name
				) {
					return self::SEND_SUCCESS;
				}
			}
		}

		return self::SEND_ERROR;
	}

	/**
	 * AJAX action handler for newsletter subscription action.
	 */
	public static function send_message() {
		if ( ! check_ajax_referer( self::FIELD_NAME, self::NONCE_KEY, true ) ) {
			wp_send_json_error( [ 'error_code' => self::NONCE_FAILED ] );
		}

		if ( Google_Recaptcha::is_configured() ) {
			$token = empty( $_POST['token'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['token'] ) ) );
			if ( ! $token ) {
				wp_send_json_error( [ 'error_code' => self::RECAPTCHA_MISSING ] );
			}
			$ip_address = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );

			$recaptcha_result = Google_Recaptcha::verify( $token, $ip_address );
			if ( ! $recaptcha_result ) {
				wp_send_json_error( [ 'error_code' => self::RECAPTCHA_FAILED ] );
			}
		}

		$email       = empty( $_POST['email'] ) ? '' : trim( sanitize_email( wp_unslash( $_POST['email'] ) ) );
		$first_name  = empty( $_POST['first_name'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) );
		$last_name   = empty( $_POST['last_name'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) );
		$affiliation = empty( $_POST['affiliation'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['affiliation'] ) ) );

		$message    = empty( $_POST['message'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['message'] ) ) );
		$newsletter = ! empty( $_POST['newsletter'] );
		$supporter  = ! empty( $_POST['supporter'] );
		$adoption   = ! empty( $_POST['adoption'] );

		$subject = 'Welcome to the Better Deal for Data Community!';

		$result = self::add( $email, $first_name, $last_name, $affiliation, $message, $newsletter, $supporter, $adoption );
		if ( $email ) {
			$body = self::message_body( $message, $newsletter, $supporter, $adoption );
			if ( self::SEND_SUCCESS === $result ) {
				$email_sent = self::send_confirmation_message( $email, $subject, $body );
				if ( ! $email_sent ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of email errors for debugging.
					error_log( 'BD4D contact form email send failed for: ' . $email );
					$result = self::SEND_ERROR;
				} else {
					wp_send_json_success();
				}
			}
		} elseif ( self::SEND_SUCCESS === $result ) {
				wp_send_json_success();
				return;
		}

		$data = [ 'error_code' => $result ];
		if ( self::JSON_ERROR === $result ) {
			$data['error_message'] = json_last_error();
		}

		wp_send_json_error( $data );
	}

	/**
	 * Send the user a message.
	 *
	 * @param string $recipient              User's email address.
	 * @param string $subject                Subject line.
	 * @param string $body                   Message text.
	 */
	public static function send_confirmation_message( $recipient, $subject, $body ) {
		return wp_mail( $recipient, $subject, $body ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
	}

	/**
	 * Generate the message text.
	 *
	 * The variables aren't "unused" -- they're in scope for the include() call.
	 *
	 * @param string  $comment                User's comment.
	 * @param boolean $newsletter             User opted in to newsletter.
	 * @param boolean $supporter              User opted in as supporter.
	 * @param boolean $adoption               User wants to learn about adopting BD4D.
	 */
	public static function message_body( $comment, $newsletter, $supporter, $adoption ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		ob_start();
		include dirname( __DIR__ ) . '/template-parts/auto-reply.php';
		return trim( ob_get_clean() );
	}


	/**
	 * Get the template part in an output buffer and return it.
	 *
	 * @param string $template_name Template file name.
	 */
	public static function get_template_part( $template_name ) {
		$located = dirname( __DIR__ ) . '/' . $template_name;

		ob_start();
		require $located; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		return ob_get_clean();
	}

	/**
	 * Render email-only airtable form.
	 *
	 * @return string
	 */
	public static function render_email_form() {
		$errors = [];
		if ( ! self::is_configured( $errors ) ) {
			return 'Incomplete configuration. Missing: ' . join( ', ', $errors );
		}

		return self::get_template_part( 'template-parts/form-email.php' );
	}
}

add_action( 'after_setup_theme', [ 'BD4D', 'hooks' ] );
