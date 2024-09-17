<?php
/**
 * Configure the Newsletter settings page.
 *
 * @package LandPKS
 * @since   1.0.0
 */

/**
 * Newsletter class.
 *
 * Implements the Newsletter settings page (Airtable and Google ReCAPTCHA).
 */
class Newsletter_Settings extends Settings {

	const PAGE_TITLE  = 'Newsletter';
	const FIELD_GROUP = 'newsletter_fields';

	/**
	 * Add Newsletter submenu page.
	 */
	public static function register_menu() {
		add_options_page( 'Newsletter', 'Newsletter', 'edit_pages', 'google', [ get_called_class(), 'page' ] );
	}

	/**
	 * Configure Newsletter form settings.
	 */
	public static function register_settings() {
		add_settings_section(
			'airtable_section',
			'Airtable',
			false,
			static::FIELD_GROUP
		);

		add_settings_section(
			'recaptcha_section',
			'ReCAPTCHA',
			false,
			static::FIELD_GROUP
		);

		$fields = [
			[
				'uid'         => 'bd4d_airtable_base_id',
				'label'       => 'Base ID',
				'section'     => 'airtable_section',
				'type'        => 'text',
				'placeholder' => 'Base ID',
				'label_for'   => 'bd4d_airtable_base_id',
				'args'        => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
			[
				'uid'         => 'bd4d_airtable_table_id',
				'label'       => 'Table ID',
				'section'     => 'airtable_section',
				'type'        => 'text',
				'placeholder' => 'Table ID',
				'label_for'   => 'bd4d_airtable_table_id',
				'args'        => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
			[
				'uid'         => 'bd4d_airtable_token',
				'label'       => 'API Authorization Token',
				'section'     => 'airtable_section',
				'type'        => 'text',
				'placeholder' => 'API Authorization Token',
				'label_for'   => 'bd4d_recaptcha_site_key',
				'args'        => [ 'bd4d_airtable_token' => 'sanitize_text_field' ],
			],
			[
				'uid'         => 'bd4d_recaptcha_site_key',
				'label'       => 'Site Key',
				'section'     => 'recaptcha_section',
				'type'        => 'text',
				'placeholder' => 'Site Key',
				'label_for'   => 'bd4d_recaptcha_site_key',
				'args'        => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
			[
				'uid'         => 'bd4d_recaptcha_secret_key',
				'label'       => 'Secret Key',
				'section'     => 'recaptcha_section',
				'type'        => 'text',
				'placeholder' => 'Secret Key',
				'label_for'   => 'bd4d_recaptcha_secret_key',
				'args'        => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
		];

		self::configure_fields( $fields, static::FIELD_GROUP );
	}
}

add_action( 'after_setup_theme', [ 'Newsletter_Settings', 'hooks' ] );
