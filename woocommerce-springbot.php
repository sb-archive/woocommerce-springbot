<?php
/**
 * @package Woocommerce_Springbot
 * @version 0.1
 */
/*
Plugin Name: Springbot WooCommerce Integration
Description: Integration plugin between WooCommerce and Springbot
Author: Springbot
Version: 0.0.3
Author URI: https://www.springbot.com
*/

require_once( __DIR__ . '/config/springbot_config.php' );

add_action( 'plugins_loaded', array( 'WooCommerce_Springbot', 'init' ) );

/**
 * Helper function to determine if WooCommerce is active
 */
function springbot_check_if_woo_active() {
	return in_array(
		'woocommerce/woocommerce.php',
		apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
	);
}

if ( ! class_exists( 'Springbot_Redirect' ) ) {

	class WooCommerce_Springbot {

		public static function init() {
			return new WooCommerce_Springbot();
		}

		public function __construct() {

			if ( $this->springbot_requirements_met() ) {

				if ( is_admin() ) {
					require_once( __DIR__ . '/classes/springbot_activation.php' );
					require_once( __DIR__ . '/classes/springbot_options.php' );
					if ( class_exists( 'Springbot_Activation' ) ) {
						$springbot_activation = new Springbot_Activation;

						if ( class_exists( 'Springbot_Options' ) ) {
							$springbot_options = new Springbot_Options( $springbot_activation );
						}
					}

					add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ),
						array( $this, 'plugin_add_settings_link' ) );

				} else {
					require_once( __DIR__ . '/classes/springbot_footer.php' );
					require_once( __DIR__ . '/classes/springbot_redirect.php' );

					if ( class_exists( 'Springbot_Footer' ) ) {
						$springbot_footer = new Springbot_Footer;
						add_action( 'wp_footer', array( $springbot_footer, 'show_async_script' ) );
					}
					if ( class_exists( 'Springbot_Redirect' ) ) {
						$springbot_redirect = new Springbot_Redirect;
					}
				}

				// Load all the webhooks
				require_once( __DIR__ . '/classes/springbot_webhooks.php' );
				if ( class_exists( 'Springbot_Webhooks' ) ) {
					$springbot_webhooks = new Springbot_Webhooks();
				}

			} else {

				require_once( dirname( __FILE__ ) . '/views/springbot-requirements-error.php' );

			}
		}

		/**
		 * Add the link to the Springbot plugin on the settings page
		 */
		public function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="plugins.php?page=springbot">' . __( 'Sync' ) . '</a>';
			array_push( $links, $settings_link );

			return $links;
		}

		/**
		 * Check that the local system meets the minimum requirements for this plugin
		 */
		public function springbot_requirements_met() {
			global $wp_version;
			if ( version_compare( PHP_VERSION, SPRINGBOT_REQUIRED_PHP_VERSION, '<' ) ) {
				return false;
			}
			if ( version_compare( $wp_version, SPRINGBOT_REQUIRED_WP_VERSION, '<' ) ) {
				return false;
			}
			if ( ! springbot_check_if_woo_active() ) {
				return false;
			}

			return true;
		}

	}
}
