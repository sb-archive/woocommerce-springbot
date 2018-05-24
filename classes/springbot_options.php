<?php

if ( ! class_exists( 'Springbot_Options' ) ) {

	class Springbot_Options {

		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;

		/**
		 * Start up
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'admin_post_process_form',  'process_form_data' );
		}



		/**
		 * Add options page
		 */
		public function add_plugin_page() {
			// This page will be under "Settings"
			add_options_page(
				'Springbot',
				'Springbot',
				'manage_options',
				'springbot',
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Options page callback
		 */
		public function create_admin_page() {
			$this->options = get_option( 'springbot_option_name' );
			echo '<div class="wrap">';
			echo '<h1>Springbot Sync</h1>';
			echo '<form method="post" action="options.php">';
			settings_fields( 'springbot_option_group' );
			do_settings_sections( 'springbot-setting-admin' );
			submit_button();
			echo '</form>';
			echo '</div>';
		}

		/**
		 * Register and add settings
		 */
		public function page_init() {
			register_setting(
				'springbot_option_group',
				'springbot_option_name',
				array( $this, 'sanitize' )
			);

			add_settings_section(
				'setting_section_id',
				'Sync with Springbot',
				array( $this, 'print_section_info' ),
				'springbot-setting-admin'
			);

			add_settings_field(
				'springbot-email',
				'Email',
				array( $this, 'email_callback' ),
				'springbot-setting-admin',
				'setting_section_id'
			);

			add_settings_field(
				'springbot-password',
				'Password',
				array( $this, 'password_callback' ),
				'springbot-setting-admin',
				'setting_section_id'
			);
		}

		/**
		 * Sanitize each setting field as needed
		 */
		public function sanitize( $input ) {
			$new_input = array();
			if ( isset( $input['id_number'] ) ) {
				$new_input['id_number'] = absint( $input['id_number'] );
			}

			if ( isset( $input['title'] ) ) {
				$new_input['title'] = sanitize_text_field( $input['title'] );
			}

			return $new_input;
		}

		/**
		 * Print the Section text
		 */
		public function print_section_info() {
			print 'Enter your Springbot credentials below to sync with Springbot:';
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function email_callback() {
			printf(
				'<input type="text" id="id_number" name="springbot_option_name[email]" value="%s" />',
				isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number'] ) : ''
			);
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function password_callback() {
			printf(
				'<input type="text" id="title" name="springbot_option_name[password]" value="%s" />',
				isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
			);
		}

	}

}

 function process_form_data() {
	var_dump( $_POST );
	die;
}