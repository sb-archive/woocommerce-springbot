<?php
/**
 * @package Woocommerce_Springbot
 * @version 0.1
 */
/*
Plugin Name: WooCommerce Springbot Integration
Description: Integration plugin between WooCommerce and Springbot
Author: Evan Jacobs, Springbot
Version: 0.1
Author URI: https://www.springbot.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WSI_NAME', 'WooCommerce Springbot Integration' );
define( 'WSI_REQUIRED_PHP_VERSION', '5.3' );
define( 'WSI_REQUIRED_WP_VERSION', '3.1' );

function wsi_requirements_met() {
	global $wp_version;
	if ( version_compare( PHP_VERSION, WSI_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}
	if ( version_compare( $wp_version, WSI_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function wsi_requirements_error() {
	global $wp_version;
	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise
 * older PHP installations could crash when trying to parse it.
 */
if ( wsi_requirements_met() ) {
	require_once( __DIR__ . '/classes/wordpress_springbot_integration.php' );
	if ( class_exists( 'WooCommerce_Springbot_Integration' ) ) {
		$GLOBALS['wsi'] = WooCommerce_Springbot_Integration::get_instance();
		register_activation_hook( __FILE__, array( $GLOBALS['wsi'], 'activate' ) );
		// register_deactivation_hook( __FILE__, array( $GLOBALS['wsi'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'wsi_requirements_error' );
}