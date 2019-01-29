<?php

if ( ! class_exists( 'Springbot_Footer' ) ) {

	class Springbot_Footer {

		/**
		 * Load all footer scripts and pixels
		 */
		public function show_async_script() {
			global $product, $wp;

			if ( $guid = $this->get_guid() ) {

				// Load the async script from Springbot
				echo "<script type=\"text/javascript\">\n";
				echo "  var _sbparams = _sbparams || [];\n";
				echo "  (function () {\n";
				echo "   var sb = document.createElement('script');\n";
				echo "   var fs = document.getElementsByTagName('script')[0];\n";
				echo "   sb.type = 'text/javascript';\n";
				echo "   sb.async = true;\n";
				echo "   sb.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + '" . SPRINGBOT_ASSETS_DOMAIN . "/async/preload/{$guid}.js'\n";
				echo "   fs.parentNode.insertBefore(sb, fs);\n";
				echo "  })();\n";
				echo "</script>\n";

				// Load the view pixel if on a product page
				if ( $product instanceof WC_Product ) {
					echo "<img src=\"" . SPRINGBOT_WOO_ETL . "/pixel/view"
					     . "?guid=" . $this->get_guid()
					     . "&pageurl=" . urlencode( $product->get_permalink() )
					     . "&product_id=" . $product->get_id()
					     . "&sku=" . urlencode( $product->get_sku() ) . "\""
					     . "style=\"position:absolute; visibility:hidden\">\n";

					// Set the product_id for our async script to use if needed
					echo "<script type=\"text/javascript\">\n";
					echo "  var Springbot = Springbot || {};\n";
					echo "  Springbot.product_id = \"{$product->get_id()}\";\n";
					echo "</script>\n";
				}

				// Load AdRoll conversion tracking on checkout success (aka order received) page
				if ( is_order_received_page() ) {
					$order_id = isset( $wp->query_vars['order-received'] ) ? intval( $wp->query_vars['order-received'] ) : null;
					if ( $order_id ) {
						$order = new WC_Order( $order_id );
						if ( $order instanceof WC_Order ) {
							echo "<script type=\"text/javascript\">\n";
							echo "  adroll_conversion_value = {$order->get_total()};\n";
							echo "  adroll_currency = \"{$order->get_currency()}\";\n";
							echo "</script>\n";
						}
					}
				}

			}
		}

		/**
		 * Get the GUID from the springbot user
		 */
		private function get_guid() {
			if ( $user = get_user_by( 'login', 'springbot' ) ) {
				$guid = get_user_meta( $user->ID, 'springbot_store_guid', true );
				$guid = strtolower( $guid );
				$guid = str_replace( '-', '', $guid );

				return $guid;
			}

			return null;
		}

	}

}
