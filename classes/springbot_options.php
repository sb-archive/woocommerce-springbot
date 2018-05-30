<?php

if ( ! class_exists( 'Springbot_Options' ) ) {

	class Springbot_Options {

		private $messages = array(
			'404'     => 'There was a problem reaching the Springbot API',
			'401'     => 'Invalid Springbot credentials',
			'default' => 'An unknown error occurred.',
		);

		/**
		 * Springbot_Options constructor.
		 */
		public function __construct( Springbot_Activation $activation ) {

			add_action( 'admin_menu', array( $this, 'add_sync_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'admin_notices', array( $this, 'my_error_notice' ) );

			if ( current_user_can( 'activate_plugins' ) ) {
				if ( isset( $_POST['springbot']['email'] ) ) {
					$code = $activation->register( $_POST['springbot']['email'], $_POST['springbot']['password'] );
					if ($code >= 400) {
						$redirect = 'plugins.php';
						$redirect = add_query_arg( 'msg', $code, $redirect );
						$redirect = add_query_arg( 'page', 'springbot', $redirect );
						wp_redirect( $redirect );
						exit;
                    }
				}
			}
		}

		/**
		 * Add the "sync" page to the plugin list
		 */
		public function add_sync_page() {
			add_plugins_page(
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
		 * Show an the appropriate error message on failure
		 */
		function my_error_notice() {
		    if (isset($_GET['msg'])) {
			    if ( isset( $this->messages[ $_GET['msg'] ] ) ) {
				    $message = $this->messages[ $_GET['msg'] ];
			    } else {
				    $message = $this->messages['default'];
			    }
			    ?>
                <div class="error notice">
                    <p><?php _e( $message, 'my_plugin_textdomain' ); ?></p>
                </div>
			    <?php
            }
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
		 * Print the Section text
		 */
		public function print_section_info() {
			echo 'Enter your Springbot credentials below to sync with Springbot:';
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function email_callback() {
			echo '<input type="text" id="email" name="springbot[email]" value="" />';
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function password_callback() {
			echo '<input type="password" id="password" name="springbot[password]" value="" />';
		}


	}

}
