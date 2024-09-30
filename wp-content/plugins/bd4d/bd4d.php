<?php
/**
 * A Better Deal for Data WordPress plugin.
 *
 * @package BD4D
 */

/**
 * Plugin Name:       A Better Deal for Data
 * Plugin URI:        https://bd4d.org/
 * Description:       A Better Deal for Data
 * Author:            Tech Matters, paulschreiber
 * Text Domain:       bd4d
 * Domain Path:       /languages
 * Version:           1.0.0
 * Requires at least: 6.2
 *
 * @package         BD4D
 */

defined( 'ABSPATH' ) || exit;
define( 'BD4D_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/class-bd4d.php';
require_once __DIR__ . '/includes/settings/class-settings.php';
require_once __DIR__ . '/includes/settings/class-contact-form-settings.php';

// Google integration.
require_once __DIR__ . '/includes/class-google-recaptcha.php'; // Must be after settings page.
