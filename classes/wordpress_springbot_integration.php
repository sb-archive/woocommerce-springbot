<?php

if ( ! class_exists( 'WooCommerce_Springbot_Integration' ) ) {

	/**
	 * Main / front controller class
	 */
	class WooCommerce_Springbot_Integration {

		const PREFIX = 'wsi_';
		const DEBUG_MODE = false;

		private static $instance;


		/**
		 * Constructor
		 */
		protected function __construct() {
			self::$instance = $this;
		}

		/**
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			echo "Activated\n";
		}

		public static function get_instance() {
			if ( isset( self::$instance ) ) {
				return self::$instance;
			}

			return new WooCommerce_Springbot_Integration();
		}

	}

}