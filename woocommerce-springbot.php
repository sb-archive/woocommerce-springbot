<?php
/**
 * Plugin Name: Springbot WooCommerce Integration
 * Plugin URI: https://www.springbot.com/
 * Description: Integration plugin between WooCommerce and Springbot
 * Version: 0.0.14
 * Author: Springbot
 *
 * @package Woocommerce_Springbot
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

if ( ! class_exists( 'WooCommerce_Springbot' ) ) {

	class WooCommerce_Springbot {

		public static function init() {
			return new WooCommerce_Springbot();
		}

		public function __construct() {

			require_once( __DIR__ . '/classes/springbot_activation.php' );
			require_once( __DIR__ . '/classes/springbot_cart.php' );
			require_once( __DIR__ . '/classes/springbot_product.php' );
			require_once( __DIR__ . '/classes/springbot_footer.php' );
			require_once( __DIR__ . '/classes/springbot_options.php' );
			require_once( __DIR__ . '/classes/springbot_user_options.php' );
			require_once( __DIR__ . '/classes/springbot_redirect.php' );
			require_once( __DIR__ . '/classes/springbot_webhooks.php' );

			if ( $this->springbot_requirements_met() ) {

				if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['HTTP_AUTH'] ) ) {
					$headerParts = explode( ' ', $_SERVER['HTTP_AUTH'] );
					if ( count( $headerParts ) === 2 ) {
						$authParts = explode( ':', base64_decode( $headerParts[1] ) );
						if ( count( $authParts ) === 2 ) {
							$_SERVER['PHP_AUTH_USER']      = $authParts[0];
							$_SERVER['PHP_AUTH_PW']        = $authParts[1];
							$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_AUTH'];
						}
					}
				}

				if ( class_exists( 'Springbot_Webhooks' ) ) {
					$springbot_webhooks = new Springbot_Webhooks();
				}

				if ( is_admin() ) {

					add_action( 'admin_menu', array( $this, 'springbot_menu_page' ) );
					add_action( 'admin_menu', array( $this, 'springbot_options_page' ) );
					add_action( 'pre_user_query', array( 'WooCommerce_Springbot', 'hide_springbot_api_user' ) );

				} else {

					if ( class_exists( 'Springbot_Footer' ) ) {
						$springbot_footer = new Springbot_Footer;
						add_action( 'wp_footer', array( $springbot_footer, 'show_async_script' ) );
					}
					if ( class_exists( 'Springbot_Redirect' ) ) {
						$springbot_redirect = new Springbot_Redirect;
						add_action( 'wp_loaded', array( $springbot_redirect, 'handle_redirect_posts' ) );
					}
					if ( class_exists( 'Springbot_Product' ) ) {
						$springbot_product = new Springbot_Product;
						add_action( 'wp_loaded', array( $springbot_product, 'handle_product_endpoint' ) );
					}
					if ( class_exists( 'Springbot_Cart' ) ) {
						$springbot_cart = new Springbot_Cart;
						add_action( 'woocommerce_cart_loaded_from_session', array(
							$springbot_cart,
							'handle_cart_endpoint'
						) );
					}
				}

			} else {

				require_once( dirname( __FILE__ ) . '/views/springbot-requirements-error.php' );

			}
		}

		// Hide the springbot user that we need for API access, so that it doesn't get deleted or a session timeout
		public static function hide_springbot_api_user( $user_search ) {
			global $wpdb;
			$user_search->query_where = str_replace( 'WHERE 1=1',
				"WHERE 1=1 AND {$wpdb->users}.user_login != '" . SPRINGBOT_WP_USER . "'", $user_search->query_where );
		}

		public function springbot_options_page() {
			if ( class_exists( 'Springbot_Activation' ) ) {
				$springbot_activation = new Springbot_Activation;
				if ( class_exists( 'Springbot_User_Options' ) ) {
					$springbot_options = new Springbot_User_Options( $springbot_activation );
					add_submenu_page(
						null,
						'Springbot Sync Options',
						'Options',
						'manage_options',
						'springbot-options',
						array( $springbot_options, 'create_admin_page' )
					);
				}
			}
		}

		public function springbot_menu_page() {

			if ( class_exists( 'Springbot_Activation' ) ) {
				$springbot_activation = new Springbot_Activation;

				if ( class_exists( 'Springbot_Options' ) ) {
					$springbot_options = new Springbot_Options( $springbot_activation );
					add_menu_page(
						'Springbot',
						'Springbot',
						'manage_options',
						'springbot',
						array( $springbot_options, 'create_admin_page' ),
						'data:image/svg+xml;base64,' . base64_encode( '<?xml version="1.0" encoding="utf-8"?>
							<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
								 viewBox="0 0 46.8 40.9" style="enable-background:new 0 0 46.8 40.9;" xml:space="preserve">
							<path id="Fill-15" class="st0" d="M18,15.1c-2.6,2.7-4.7,5.8-5.8,9.4c-1.5,4.7-1,9.5,1.4,12.2c1.2,1.3,2.5,1.8,4,1.5
								c2.6-0.6,5.1-3.5,6.3-7.2c0.7-2.4,1.8-8.6-3.7-14.3C19.4,16,18.7,15.5,18,15.1 M16.7,40.9c-1.9,0-3.7-0.9-5.1-2.5
								c-3-3.4-3.7-9-1.9-14.7c1.2-3.6,3.1-6.7,5.6-9.5c-1.3-0.1-2.4,0.1-3.4,0.5C6.6,16.6,2.1,22.3,1.6,29C1.5,30.9,0,30.4,0,29.7
								c0.5-7.6,4.8-15.1,11-17.4c2.2-0.8,4.4-0.9,6.4-0.3C24.2,5.5,37.1,1.6,44.9,0.1C47.7-0.4,47.2,1,45,1.5c-5.8,1.2-19.1,5.8-24.9,11.6
								c0.7,0.5,1.3,1.2,2,1.8c4.5,4.7,6.1,10.9,4.3,16.8c-1.4,4.6-4.7,8.2-8.2,8.9C17.6,40.8,17.2,40.9,16.7,40.9"/>
							</svg>
						' )
					);
				}
			}
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
