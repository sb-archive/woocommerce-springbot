<?php
/**
 * @package Woocommerce_Springbot
 * @version 0.1
 */
/*
Plugin Name: Springbot WooCommerce Integration
Description: Integration plugin between WooCommerce and Springbot
Author: Springbot
Version: 0.1
Author URI: https://www.springbot.com
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once( __DIR__ . '/config/springbot_config.php' );

/**
 * Check that requirements are met and register hooks/actions
 */
if ( springbot_requirements_met() ) {

	if ( is_admin() ) {
		require_once( __DIR__ . '/classes/springbot_activation.php' );
		require_once( __DIR__ . '/classes/springbot_menu.php' );
		if ( class_exists( 'Springbot_Activation' ) ) {
			$springbot_activation = new Springbot_Activation;
			register_activation_hook( __FILE__, array( $springbot_activation, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $springbot_activation, 'deactivate' ) );
		}
		if ( class_exists( 'Springbot_Menu' ) ) {
			$springbot_menu = new Springbot_Menu;
			add_action( 'admin_menu', array( $springbot_menu, 'add_springbot_menu' ) );
		}
	} else {
		require_once( __DIR__ . '/classes/springbot_footer.php' );
		if ( class_exists( 'Springbot_Footer' ) ) {
			$springbot_footer = new Springbot_Footer;
			add_action( 'wp_footer', array( $springbot_footer, 'show_async_script' ) );
		}
	}

} else {
	require_once( dirname( __FILE__ ) . '/views/springbot-requirements-error.php' );
}

/**
 * Check that the local system meets the minimum requirements for this plugin
 */
function springbot_requirements_met() {
	global $wp_version;
	if ( version_compare( PHP_VERSION, SPRINGBOT_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}
	if ( version_compare( $wp_version, SPRINGBOT_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}
	return true;
}

