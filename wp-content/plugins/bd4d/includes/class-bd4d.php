<?php
/**
 * Contact Form for BD4D
 *
 * @package LandPKS
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
	const MISSING_EMAIL     = 6;
	const RECAPTCHA_MISSING = 7;
	const RECAPTCHA_FAILED  = 8;
	const NONCE_FAILED      = 9;


	const FIELD_NAME = 'newsletter_form';
	const NONCE_KEY  = 'newsletter_form_nonce';

	const SOURCES = [
		'Word of Mouth'             => 'Word of mouth',
		'Tech Matters'              => 'Tech Matters',
		'Online Search'             => 'Online search',
		'Event'                     => 'Event',
		'Social Media'              => 'Social media',
		'Article, Blog, or Podcast' => 'Aricle, blog, or podcast',
		'Other'                     => 'Other',
	];

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_shortcode( 'contact-form', [ __CLASS__, 'render_email_form' ] );
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
					self::MISSING_EMAIL     => 'Please supply an email address',
					self::RECAPTCHA_MISSING => 'The ReCATCHA token was missing.',
					self::RECAPTCHA_FAILED  => 'ReCAPTCHA could not validate you are a human.',
					self::NONCE_FAILED      => 'WordPress could not validate you are a human.',
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
	 * Add the user to a Airtable table.
	 *
	 * @param string  $email              User's email address.
	 * @param string  $first_name         User's first name.
	 * @param string  $last_name          User's last name.
	 * @param string  $affiliation        User's company or organization.
	 * @param string  $source             User's referral source.
	 * @param string  $message            User's message.
	 * @param boolean $newsletter         Whether or not to subscribe to the newsletter.
	 * @param boolean $supporter          Whether or not to identify the user as a supporter.
	 */
	public static function add( $email, $first_name = false, $last_name = false, $affiliation = false, $source = false, $message = false, $newsletter = false, $supporter = false ) {
		if ( ! $email ) {
			return self::MISSING_EMAIL;
		}

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
		if ( $source ) {
			$data['fields']['Source'] = $source;
		}
		if ( $message ) {
			$data['fields']['Form Comments'] = $message;
		}
		
		$data['fields']['Email-Opted In?'] = $newsletter;
		$data['fields']['CotW-OptedIn?']   = $supporter;

		$raw_result = wp_remote_post(
			self::BASE_URL . '/' . self::base_id() . '/' . self::table_id(),
			[
				'headers' => self::headers(),
				'body'    => wp_json_encode( $data ),
			]
		);

		$result        = json_decode( $raw_result['body'], true );
		$http_response = $raw_result['response'];

		if ( ! $result ) {
			return self::JSON_ERROR;
		}
		if ( isset( $http_response['code'] ) && 200 === $http_response['code'] ) {
			$id = $result['id'];
			if ( $id ) {
				if ( $result['fields']['First Name'] === $first_name &&
				$result['fields']['Last Name'] === $last_name &&
				$result['fields']['Email Address'] === $email
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

		// Sanitize each item in the array. Confirm it's in the list of allowed items.
		$source = empty( $_POST['source'] ) ? '' : array_filter(
			array_map(
				function ( $data ) {
					$item = trim( sanitize_text_field( wp_unslash( $data ) ) );
					return array_key_exists( $item, self::SOURCES ) ? $item : '';
				},
				$_POST['source']  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			)
		);

		$message    = empty( $_POST['message'] ) ? '' : trim( sanitize_text_field( wp_unslash( $_POST['message'] ) ) );
		$newsletter = ! empty( $_POST['newsletter'] );
		$supporter  = ! empty( $_POST['supporter'] );

		$subject = 'Welcome to a Better Deal for Data!';

		$result = self::add( $email, $first_name, $last_name, $affiliation, $source, $message, $newsletter, $supporter );
		$body   = self::message_body( $email, $message, $newsletter, $supporter );
		if ( self::SEND_SUCCESS === $result ) {
			self::send_confirmation_message( $email, $subject, $body );
			wp_send_json_success();
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
		wp_mail( $recipient, $subject, $body ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
	}

	/**
	 * Generate the message text
	 *
	 * @param string $email                  User's email address.
	 * @param string $comment                User's comment.
	 * @param string $newsletter             User opted in to newsletter.
	 * @param string $supporter              User opted in as supporter.
	 */
	public static function message_body( $email, $comment, $newsletter, $supporter ) {
		return "hi mom [$email, $comment, $newsletter, $supporter";
	}


	/**
	 * Get the template part in an output buffer and return it
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
		return self::get_template_part( 'template-parts/form-email.php' );
	}
}

add_action( 'after_setup_theme', [ 'BD4D', 'hooks' ] );
