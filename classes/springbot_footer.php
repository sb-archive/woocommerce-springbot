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
				echo "<script data-cfasync=\"false\" type=\"text/javascript\">\n";
				echo "  var _sbparams = _sbparams || [];\n";
				echo "  (function () {\n";
				echo "   var sb = document.createElement('script');\n";
				echo "   var fs = document.getElementsByTagName('script')[0];\n";
				echo "   sb.type = 'text/javascript';\n";
				echo "   sb.async = true;\n";
				echo "   sb.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + '" . SPRINGBOT_ASSETS_DOMAIN . "/async/preload/" . esc_attr($guid) . ".js'\n";
				echo "   fs.parentNode.insertBefore(sb, fs);\n";

				// Load the view pixel if on a product page
				if ( is_product() && ( $product instanceof WC_Product ) ) {
					echo "window.addEventListener('load', function(event) {\n";
					echo "  var pixelContainer = document.createElement(\"div\");\n";
					echo "  var pixel = document.createElement(\"IMG\");\n";
					echo "  pixel.setAttribute(\"style\", \"height: 1px; width: 1px; position:absolute; visibility:hidden\");\n";
					echo "  pixel.setAttribute(\"src\", \"". SPRINGBOT_WOO_ETL ."/pixel/view"
					     . "?guid=". esc_attr($this->get_guid())
					     . "&sku=". urlencode( $product->get_sku() )
					     . "&product_id=". esc_attr($product->get_id())
					     . "&pageurl=". urlencode( $product->get_permalink() )
					     . "&uuid=\"+SB.util.uuid());\n";
					echo "  pixel.className = 'sb-pixel';\n";
					echo "  pixelContainer.appendChild(pixel);\n";
					echo "  document.body.appendChild(pixelContainer);\n";
					echo "});\n";

				}

				echo "  })();\n";
				echo "</script>\n";

				if ( is_product() && ( $product instanceof WC_Product ) ) {
					// Set the product_id for our async script to use if needed
					echo "<script data-cfasync=\"false\" type=\"text/javascript\">\n";
					echo "  var Springbot = Springbot || {};\n";
					echo "  Springbot.product_id = \"". esc_attr($product->get_id()) ."\";\n";
					echo "</script>\n";
				}
				
				// Load AdRoll conversion tracking on checkout success (aka order received) page
				if ( is_order_received_page() ) {
					if(empty( $_GET['key'] )) {
						$order_id = isset( $wp->query_vars['order-received'] ) ? intval( $wp->query_vars['order-received'] ) : null;
					} else {
						$order_id = wc_get_order_id_by_order_key( sanitize_text_field($_GET['key']) );
					}
					if ( $order_id ) {
						$order = new WC_Order( $order_id );
						if ( $order instanceof WC_Order ) {
							echo "<script type=\"text/javascript\">\n";
							echo "  adroll_conversion_value = ". esc_attr($order->get_total()). ";\n";
							echo "  adroll_currency = \"". esc_attr($order->get_currency()) ."\";\n";
							echo "window._sb_conversion = {\n";
							echo "id: ". esc_attr($order_id) .",\n";
							echo "total: ". esc_attr($order->get_total()) .",\n";
							echo "ip: \"". esc_attr($this->getIp()) ."\",\n";
							echo "agent: \"". _e($this->getUserAgent()) ."\",\n";
							echo "ookie: document.cookie.match('(^|;) ?__xlid=([^;]*)(;|$)') ? document.cookie.match('(^|;) ?__xlid=([^;]*)(;|$)')[2] : null\n";
							echo "}\n";
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
			if ( $user = get_user_by( 'login', SPRINGBOT_WP_USER ) ) {
				$guid = get_user_meta( $user->ID, 'springbot_store_guid', true );
				$guid = strtolower( $guid );
				$guid = str_replace( '-', '', $guid );

				return $guid;
			}

			return null;
		}

		/**
	     * @return string
	     */
	    private function getIp()
	    {
	        if (!empty($_SERVER['REMOTE_ADDR'])) {
	            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
	        } else {
	            return "";
	        }
	    }

	    /**
	     * @return string
	     */
	    private function getUserAgent()
	    {
	        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
	            return sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
	        } else {
	            return "";
	        }
	    }
	}

}
