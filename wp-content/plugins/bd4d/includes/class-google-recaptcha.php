<?php
/**
 * Initialize and verify Google ReCAPTCHA.
 *
 * @package BD4D
 * @since 1.0.0
 */

/**
 * ReCAPTCHA class.
 *
 * Implements ReCAPTCHA loading and verification.
 */
class Google_Recaptcha {

	const API_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'wp_enqueue_scripts', [ 'Google_Recaptcha', 'wp_enqueue_scripts' ] );
	}

	/**
	 * ReCAPTCHA Site Key.
	 *
	 * @return string
	 */
	public static function get_site_key() {
		return get_option( 'bd4d_recaptcha_site_key' );
	}

	/**
	 * ReCAPTCHA Secret Key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		return get_option( 'bd4d_recaptcha_secret_key' );
	}

	/**
	 * Whether or not ReCAPTCHA is configured (has both site key and secret key).
	 *
	 * @return boolean
	 */
	public static function is_configured() {
		return self::get_site_key() && self::get_secret_key();
	}

	/**
	 * Enqueue Google ReCAPTCHA JavaScript.
	 */
	public static function wp_enqueue_scripts() {
		if ( ! self::is_configured() ) {
			return;
		}

		// Don't set a resource version here. We don't want query parameters passed to Google.
		wp_enqueue_script( 'recaptcha', add_query_arg( 'render', self::get_site_key(), 'https://www.google.com/recaptcha/api.js' ), [], null, [] ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Verify a ReCAPTCHA response.
	 *
	 * @param string $response    The response string (token).
	 * @param string $ip_address  The client's IP address.
	 *
	 * @return boolean
	 */
	public static function verify( $response, $ip_address ) {
		if ( ! self::is_configured() ) {
			return false;
		}

		$secret = self::get_secret_key();

		$parameters = array_filter(
			[
				'secret'   => $secret,
				'response' => $response,
				'remoteip' => $ip_address,
			]
		);

		$response = wp_remote_post(
			self::API_URL,
			[
				'timeout' => 10, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- Allow for slow reCAPTCHA responses.
				'body'    => $parameters,
			]
		);

		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of reCAPTCHA errors for debugging.
			error_log( 'BD4D reCAPTCHA verify HTTP error: ' . $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of reCAPTCHA errors for debugging.
			error_log( 'BD4D reCAPTCHA verify returned HTTP ' . $response_code );
			return false;
		}

		$result_json = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of reCAPTCHA errors for debugging.
			error_log( 'BD4D reCAPTCHA verify: could not parse JSON response' );
			return false;
		}

		/* phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			Result format
			{
				"success": true|false,
				"score": number,            // v3 only: 0.0 (bot) - 1.0 (human)
				"action": string,           // v3 only: the action name
				"challenge_ts": timestamp,  // timestamp of the challenge load (ISO format yyyy-MM-dd'T'HH:mm:ssZZ)
				"hostname": string,         // the hostname of the site where the reCAPTCHA was solved
				"error-codes": [...]        // optional
			}
		*/

		$success = isset( $result_json['success'] ) && true === $result_json['success'];

		if ( ! $success ) {
			// Log the full verification result so intermittent failures (e.g.
			// 'timeout-or-duplicate' from expired tokens) can be diagnosed later.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging of reCAPTCHA failures for debugging.
			error_log(
				'BD4D reCAPTCHA verification failed: ' . wp_json_encode(
					[
						'success'      => $result_json['success'] ?? null,
						'score'        => $result_json['score'] ?? null,
						'action'       => $result_json['action'] ?? null,
						'hostname'     => $result_json['hostname'] ?? null,
						'challenge_ts' => $result_json['challenge_ts'] ?? null,
						'error_codes'  => $result_json['error-codes'] ?? [],
					]
				)
			);
		}

		return $success;
	}
}
add_action( 'after_setup_theme', [ 'Google_Recaptcha', 'hooks' ] );
