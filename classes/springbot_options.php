<?php

if ( ! class_exists( 'Springbot_Options' ) ) {

	class Springbot_Options {

		private $messages = array(
			'404'       => 'There was a problem reaching the Springbot API',
			'401'       => 'Invalid Springbot credentials',
			'not_admin' => 'You do not have permission to activate plugins',
			'default'   => 'An unknown error occurred.',
		);

		/**
		 * Springbot_Options constructor.
		 *
		 * @param Springbot_Activation $activation
		 */
		public function __construct( Springbot_Activation $activation ) {

			add_action( 'admin_menu', array( $this, 'add_sync_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'admin_notices', 'my_error_notice');

			if ( isset($_POST['springbot']['email']) && isset($_POST['springbot']['password']) ) {
				
				$email = sanitize_email($_POST['springbot']['email']);
				$password = sanitize_text_field($_POST['springbot']['password']);
			
				if (!$email) {
					$redirect = 'admin.php';
					$redirect = add_query_arg( 'msg', 'bad_email', $redirect );
					$redirect = add_query_arg( 'page', 'springbot', $redirect );
					wp_redirect( $redirect );
					exit;
				}
				if (!$password) {
					$redirect = 'admin.php';
					$redirect = add_query_arg( 'msg', 'bad_password', $redirect );
					$redirect = add_query_arg( 'page', 'springbot', $redirect );
					wp_redirect( $redirect );
					exit;
				}

				if ( ! current_user_can( 'activate_plugins' ) ) {
					$redirect = 'admin.php';
					$redirect = add_query_arg( 'msg', 'not_admin', $redirect );
					$redirect = add_query_arg( 'page', 'springbot', $redirect );
					wp_redirect( $redirect );
					exit;
				} else {
					$password = strtr(base64_encode(openssl_encrypt($password, 'aes-128-cbc', hash('sha256', 'a1a6e1tt3h1aerg8613a1rg61Sgg1h61r', true))), '+/=', '-_,');
					$code = $activation->register( $email, $password );
					if ( $code >= 400 ) {
						$redirect = 'admin.php';
						$redirect = add_query_arg( 'msg', $code, $redirect );
						$redirect = add_query_arg( 'page', 'springbot', $redirect );
						wp_redirect( $redirect );
						exit;
					}
				}
			}
		}

		/**
		 * Options page callback
		 */
		public function create_admin_page() {

			$activation = new Springbot_Activation();

			echo '<div class="wrap">';
			echo '<h1>Springbot Sync</h1>';
			if ( $activation->is_registered() ) {
				echo '<a href="https://app.springbot.com">';
				echo '<img src="' . plugins_url( '/assets/syncing.jpg', dirname( __FILE__ ) ) . '">';
				echo '</a>';
			} else {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin.php' ) ) . '?page=springbot">';
				echo '<input type="hidden" name="action" value="springbot_login">';
				settings_fields( 'springbot_option_group' );
				do_settings_sections( 'springbot-setting-admin' );
				submit_button();
				echo '</form>';
			}
			echo '</div>';
		}

		/**
		 * Show an the appropriate error message on failure
		 */
		function my_error_notice() {
			if ( isset( $_GET['msg'] ) ) {
				$msg = sanitize_text_field($_GET['msg']);
				if ( isset( $this->messages[ $msg ] ) ) {
					$message = $this->messages[ $msg ];
				} else {
					$message = $this->messages['default'];
				}
				?>
                <div class="error notice">
                    <p><?php _e( $message ); ?></p>
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
