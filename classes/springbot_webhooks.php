<?php

if ( ! class_exists( 'Springbot_Webhooks' ) ) {

	class Springbot_Webhooks {

		// An array to keep track of webhooks we've already sent during the lifetime of a request, to prevent dupes
		private static $called = array();

		public function __construct() {

			// Products
			add_action( 'woocommerce_before_product_object_save', array( $this, 'send_product_webhook' ) );

			// Categories
			add_action( 'edit_terms', array( $this, 'send_category_webhook_2' ), 10, 2 );
			add_action( 'create_term', array( $this, 'send_category_webhook_3' ), 10, 3 );
			add_action( 'delete_product_cat', array( $this, 'delete_category' ) );

			// Customers
			add_action( 'user_register', array( $this, 'send_customer_webhook' ) );
			add_action( 'profile_update', array( $this, 'send_customer_webhook_2' ), 10, 2 );
			add_action( 'delete_user', array( $this, 'delete_customer' ) );

			// Orders
			add_action( 'woocommerce_update_order', array( $this, 'send_order_webhook' ) );

			// Carts - Cart qty update only
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'send_cart_webhook_3' ), 10, 3 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'send_cart_webhook_2' ), 10, 2 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'send_cart_webhook_3' ), 10, 3 );

			// Handles deletions for products and orders
			add_action( 'wp_delete_post', array( $this, 'delete_post' ), 10, 2 );
			add_action( 'wp_trash_post', array( $this, 'trash_post' ) );

			// Save redirect ID and user agent info
			add_action( 'woocommerce_checkout_create_order', array( $this, 'save_order_meta' ), 20, 2 );

		}

		/**
		 * Called on the frontend when a user completes an order
		 *
		 * @param WC_Order $order
		 * @param array $data
		 */
		function save_order_meta( $order, $data ) {

			// Save the current user agent used to complete the order
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$order->update_meta_data( '_sb_order_user_agent', $_SERVER['HTTP_USER_AGENT'] );
			}

			// Save any redirect IDs to the order. Used for Springbot attribution
			if ( isset( $_COOKIE['springbot_redirect_queue'] ) ) {
				$order->update_meta_data( '_sb_redirect_queue', $_COOKIE['springbot_redirect_queue'] );
			}

			// Associate the cart ID to the order so we can associate the two in Springbot, then unset it
			if ( isset( $_COOKIE['sb_cart_id'] ) && ( $_COOKIE['sb_cart_id'] > 0 ) ) {
				$order->update_meta_data( '_sb_cart_id', $_COOKIE['sb_cart_id'] );
				unset( $_COOKIE['sb_cart_id'] );
				setcookie( 'sb_cart_id', null, - 1, '/' );
			}

			// Associate the user agent used on the cart to the order
			if ( isset( $_COOKIE['sb_cart_user_agent'] ) ) {
				$order->update_meta_data( '_sb_cart_user_agent', base64_decode( $_COOKIE['sb_cart_agent'] ) );
				unset( $_COOKIE['sb_cart_user_agent'] );
				setcookie( 'sb_cart_user_agent', null, - 1, '/' );
			}
		}

		/**
		 * @param int $postId
		 */
		public function delete_post( $postId ) {
			if ( $post = get_post( $postId ) ) {
				if ( $post->post_type === 'product' ) {
					$this->send_webhook( 'product', $postId, true );
				} elseif ( $post->post_type === 'shop_order' ) {
					$this->send_webhook( 'order', $postId, true );
				}
			}
		}

		/**
		 * @param int $postId
		 */
		public function trash_post( $postId ) {
			if ( $post = get_post( $postId ) ) {
				if ( $post->post_type === 'product' ) {
					$this->send_webhook( 'product', $postId, true );
				} elseif ( $post->post_type === 'shop_order' ) {
					$this->send_webhook( 'order', $postId, true );
				}
			}
		}

		/**
		 * @param int $termId
		 */
		public function delete_category( $termId ) {
			$this->send_webhook( 'categories', $termId, true );
		}

		/**
		 * @param int $userId
		 */
		public function delete_customer( $userId ) {
			$this->send_webhook( 'customers', $userId, true );
		}

		/**
		 * Send a cart webhook to Springbot
		 *
		 * @param string $hash
		 * @param int $id1
		 * @param int $id2
		 */
		public function send_cart_webhook_3( $hash, $id1, $id2 ) {
			$this->convert_and_send_cart( $hash, WC()->cart );
		}

		/**
		 * Send a cart webhook to Springbot
		 *
		 * @param string $hash
		 * @param WC_Cart $cart
		 */
		public function send_cart_webhook_2( $hash, $cart ) {
			$this->convert_and_send_cart( $hash, $cart );
		}

		/**
		 * Since carts aren't available to the API, send it up with the webhook
		 *
		 * @param string $hash
		 * @param WC_Cart $cart
		 */
		private function convert_and_send_cart( $hash, $cart ) {
			if ( $cart instanceof WC_Cart ) {
				$items = array();
				foreach ( $cart->get_cart() as $item ) {

					$data = $item['data'];
					if ( $data instanceof WC_Product ) {
						$sku = $data->get_sku();
						if ( ! $item['variation_id'] ) {
							$item['variation_id'] = null;
						} else {
							$parent = wc_get_product( $item['product_id'] );
							if ( $parent instanceof WC_Product ) {
								$sku = $parent->get_sku();
							}
						}

						$imgSrc = '';
						if ( $image = wp_get_attachment_image_src( $data->get_image_id() ) ) {
							if ( is_array( $image ) && ( count( $image ) > 0 ) ) {
								$imgSrc = $image[0];
							}
						}

						$items[] = array(
							'product_id'    => $item['product_id'],
							'product_sku'   => $sku,
							'type'          => $data->get_type(),
							'quantity'      => $item['quantity'],
							'variation_id'  => $item['variation_id'],
							'landing_url'   => $data->get_permalink(),
							'image_url'     => $imgSrc,
							'variation_sku' => $data->get_sku()
						);
					}
				}

				$customer = WC()->customer;
				if ( $customer instanceof WC_Customer ) {
					$userAgent = '';
					if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
						$userAgent = $_SERVER['HTTP_USER_AGENT'];
						setcookie( 'sb_cart_user_agent', base64_encode( $userAgent ), 0, '/' );
					}
					setcookie( 'sb_cart_id', $this->tokenToDec( $hash ), 0, '/' );
					$this->send_webhook( 'carts', $this->tokenToDec( $hash ), false, array(
						'id'         => $this->tokenToDec( $hash ),
						'hash'       => $hash,
						'email'      => $customer->get_email(),
						'first_name' => $customer->get_first_name(),
						'last_name'  => $customer->get_last_name(),
						'user_id'    => $customer->get_id(),
						'is_guest'   => ! $customer->get_email(),
						'user_agent' => $userAgent,
						'items'      => $items
					) );
				}
			}
		}

		/**
		 * Send a category webhook to Springbot
		 *
		 * @param int $termId
		 * @param string $taxonomy
		 */
		public function send_category_webhook_2( $termId, $taxonomy ) {
			if ( $taxonomy == 'product_cat' ) {
				$this->send_category( $termId );
			}
		}

		/**
		 * Send a category webhook to Springbot
		 *
		 * @param int $termId
		 * @param int $ttId
		 * @param string $taxonomy
		 */
		public function send_category_webhook_3( $termId, $ttId, $taxonomy ) {
			if ( $taxonomy == 'product_cat' ) {
				$this->send_category( $termId );
			}
		}

		/**
		 * @param int $categoryId
		 */
		private function send_category( $categoryId ) {
			$category = get_term( $categoryId );
			if ( $category instanceof WP_Term ) {
				$pathParts = array( $categoryId );
				$parent    = get_term( $category->parent );
				while ( ( $parent instanceof WP_Term ) && ! in_array( $parent->term_id, $pathParts ) ) {
					$pathParts[] = $parent->term_id;
					$parent      = get_term( $parent->parent );
				}

				$pathParts = array_reverse( $pathParts );
				$this->send_webhook( 'categories', $categoryId, false, array(
					'name' => $category->name,
					'path' => implode( '/', $pathParts ),
					'url'  => get_term_link( $categoryId, 'product_cat' )
				) );
			}
		}

		/**
		 * Send a customer webhook to Springbot
		 *
		 * @param int $customerId
		 */
		public function send_customer_webhook( $customerId ) {
			$this->send_webhook( 'customers', $customerId );
		}

		/**
		 * Send a customer webhook to Springbot
		 *
		 * @param int $customerId
		 * @param array $oldData
		 */
		public function send_customer_webhook_2( $customerId, $oldData ) {
			$this->send_webhook( 'customers', $customerId );
		}

		/**
		 * Send a product webhook to Springbot
		 *
		 * @param WC_Product $product
		 */
		public function send_product_webhook( $product ) {
			if ( $product instanceof WC_Product ) {
				$product = $product->get_id();
			}
			$this->send_webhook( 'products', $product );
		}

		/**
		 * Send an order webhook to Springbot
		 *
		 * @param int $orderId
		 */
		public function send_order_webhook( $orderId ) {
			$this->send_webhook( 'orders', $orderId );
		}

		/**
		 * Send a webhook to the Springbot API with the supplied data
		 *
		 * @param string $type
		 * @param int $id
		 * @param bool $deleted
		 * @param array $extra
		 */
		private function send_webhook( $type, $id, $deleted = false, $extra = array() ) {

			if ( ! is_numeric( $id ) ) {
				error_log( "ID is non-numeric for webhook handler, type: {$type}" );

				return;
			}

			$key = "{$type}-{$id}";
			if ( isset( self::$called[ $key ] ) ) {
				return;
			}
			self::$called[ $key ] = true;

			require_once( __DIR__ . '/../classes/springbot_activation.php' );

			$activation = new Springbot_Activation();
			if ( $activation->is_registered() ) {
				$data = array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'blocking'    => false,
					'headers'     => array(),
					'body'        => json_encode( array(
						'type'    => $type,
						'id'      => $id,
						'deleted' => $deleted,
						'extra'   => $extra
					) )
				);
				wp_remote_post( SPRINGBOT_WOO_ETL . '/woocommerce/webhooks/v1/' . $activation->get_springbot_store_id() . '/' . $type,
					$data );
			}
		}

		/**
		 * Convert a hex value to an integer value and truncate it if necessary
		 *
		 * @param string $token
		 *
		 * @return int
		 */
		private function tokenToDec( $token ) {
			if ( ! $token ) {
				return null;
			} else {
				return hexdec( substr( $token, - 7 ) );
			}
		}

	}

}
