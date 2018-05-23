<?php

if ( ! class_exists( 'Springbot_Menu' ) ) {

	/**
	 * Springbot Menu Class
	 */
	class Springbot_Menu {

		/**
		 * Display the Springbot menu items
		 */
		public function add_springbot_menu() {
			add_options_page(
				'Springbot Sync',
				'Springbot',
				'manage_options',
				'springbot-options',
				array( $this, 'springbot_options' )
			);
		}

		/**
		 * Show the Springbot options page
		 */
		public function springbot_options() {
			require_once( dirname( __FILE__ ) . '/../views/springbot-options.php' );
		}

	}

}