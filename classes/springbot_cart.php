<?php

if ( ! class_exists( 'Springbot_Cart' ) ) {

	class Springbot_Cart {

		/**
		 * Check that we are on the springbot cart endpoint
		 *
		 * @param $permalink
		 *
		 * @return mixed
		 */
		public function handle_cart_endpoint( $permalink ) {
			$uri   = $_SERVER['REQUEST_URI'];
			$parts = parse_url( $uri );
			$path  = trim( str_replace( 'index.php', '', $parts['path'] ), '/' );

			if ( $path === 'springbot/main/createcart' ) {
				$cartId = self::get_cart_id( md5( json_encode( WC()->cart->get_cart_for_session() ) ) );
				header( "Content-Type: application/json" );
				echo json_encode( array( 'cart_id' => (int) $cartId ) );
				exit;
			}

			return $permalink;
		}

		/**
		 * Display the subscribe checkbox at the end of the checkout form
		 *
		 * @return void
		 */
		public function show_subscribe_field( $checkout ) {
			$checked = $checkout->get_value( 'newsletter_subscribe' ) ? $checkout->get_value( 'newsletter_subscribe' ) : 1;
			woocommerce_form_field( 'newsletter_subscribe', array(
				'type'          => 'checkbox',
				'class'         => array('form-row-wide'),
				'label'         => __('Subscribe to Newsletter'),
			), $checked );
		}

		/**
		 * Process the subscribe checkbox once posted
		 *
		 * @return void
		 */
		function process_subscribe_field() {
			if ( $_POST['newsletter_subscribe'] ) {
				$email = $_POST['billing_email'];
				if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
					Springbot_Webhooks::send_webhook( 'subscribers', 0, false, array(
						'email' => $email,
						'first_name' => $_POST['billing_first_name'],
						'last_name' => $_POST['billing_last_name'],
					) );
				}
			}
		}

		/**
		 * Return a numeric cart token based on the cart hash
		 *
		 * @param string $hash
		 *
		 * @return int
		 */
		public static function get_cart_id( $hash ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$userAgent = $_SERVER['HTTP_USER_AGENT'];
				setcookie( 'sb_cart_user_agent', base64_encode( $userAgent ), 0, '/' );
			}
			if ( isset( $_COOKIE['sb_cart_id'] ) && is_numeric( $_COOKIE['sb_cart_id'] ) ) {
				$cartId = $_COOKIE['sb_cart_id'];
			} else {
				// Create a unique ID for this cart and save it
				$cartId = self::token_to_dec( rand() . $hash . rand() );
				setcookie( 'sb_cart_id', $cartId, 0, '/' );
			}

			return $cartId;
		}

		/**
		 * Convert a hex value to an integer value and truncate it if necessary
		 *
		 * @param string $token
		 *
		 * @return int
		 */
		private static function token_to_dec( $token ) {
			if ( ! $token ) {
				return null;
			} else {
				return hexdec( substr( $token, - 7 ) );
			}
		}

	}

}