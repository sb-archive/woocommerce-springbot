<?php

if ( ! class_exists( 'Springbot_User_Options' ) ) {

    class Springbot_User_Options {
        private $messages = array(
            '404'       => 'There was a problem reaching the Springbot API',
            '401'       => 'Invalid Springbot credentials',
            'not_admin' => 'You do not have permission to activate plugins',
            'default'   => 'An unknown error occurred.',
        );
        private $secret = '';
        private $key = '';
        private $securityKey = '';
        private $guid = '';
        private $storeId = '';
        private $newKey = '';
        private $activation;

        /**
         * Springbot_Options constructor.
         *
         * @param Springbot_Activation $activation
         */
        public function __construct( Springbot_Activation $activation ) {
            $this->activation = $activation;
            add_action( 'admin_init', array( $this, 'page_init' ) );
            add_action( 'admin_notices', array( $this, 'my_error_notice' ) );

            if ( isset( $_POST['springbot']['action'] ) ) {
                if ( ! current_user_can( 'activate_plugins' ) ) {
                    $redirect = 'admin.php';
                    $redirect = add_query_arg( 'msg', 'not_admin', $redirect );
                    $redirect = add_query_arg( 'page', 'admin', $redirect );
                    wp_redirect( $redirect );
                    exit;
                }
                if ( isset( $_POST['springbot']['re-create'] ) ) {
                    $code = $this->re_create_user_auth(
                        $_POST['springbot']['security-key'],
                        $_POST['springbot']['guid'], 
                        $_POST['springbot']['store-id']
                    );
                } else {
                    $code = $this->activation->save_springbot_data(
                        $_POST['springbot']['security-key'],
                        $_POST['springbot']['guid'], 
                        $_POST['springbot']['store-id']
                    );
                }
                
                if ( !$code ) {
                    $redirect = 'admin.php';
                    $redirect = add_query_arg( 'msg', 500, $redirect );
                    $redirect = add_query_arg( 'page', 'admin', $redirect );
                    wp_redirect( $redirect );
                    exit;
                }
                
            }
        }

        /**
         * This will re-create the user, meta data, and api integration for the store.
         *
         * @param $springbotStoreId
         * @param $guid
         * @param $securityToken
         *
         * @return bool|array
         */
        private function re_create_user_auth($securityToken, $guid, $springbotStoreId) {
            global $wpdb;
            if ($user = get_user_by( 'login', SPRINGBOT_WP_USER )) {
                $wpdb->delete($wpdb->prefix."woocommerce_api_keys", ['user_id' => $user->ID], '%d');
                // We need to delete the user because in instances where they have locked us 
                // out because of a login timeout, it will continue to log us out and cause 
                // authorization issues unless we change users.
                if( !wp_delete_user( $user->ID ) ){
                    return false;
                }
            }

            $userId = wp_insert_user( array(
                'user_login' => SPRINGBOT_WP_USER,
                'user_pass'  => $this->activation->random_password(),
                'user_email' => SPRINGBOT_WP_EMAIL,
                'role'       => 'administrator'
            ) );
            
            if (!is_numeric($userId)) {
                return false;
            }

            $consumer_key    = 'ck_' . wc_rand_hash();
            $consumer_secret = 'cs_' . wc_rand_hash();
            $description = sprintf(
                __( '%1$s - API (re-created on %2$s at %3$s).', 'woocommerce' ),
                wc_clean( 'Springbot' ),
                date_i18n( wc_date_format() ),
                date_i18n( wc_time_format() )
            );

            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                array(
                    'user_id'         => $userId,
                    'description'     => $description,
                    'permissions'     => 'read_write',
                    'consumer_key'    => wc_api_hash( $consumer_key ),
                    'consumer_secret' => $consumer_secret,
                    'truncated_key'   => substr( $consumer_key, - 7 ),
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
            );
            $this->activation->save_springbot_data($securityToken, $guid, $springbotStoreId);
            $this->newKey = $consumer_key;

            return true;
        }

        /**
         * Options page callback
         */
        public function create_admin_page() {
            echo '<div class="wrap">';
            echo '<h1>Springbot Options</h1>';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin.php' ) ) . '?page=springbot-options">';
            echo '<input type="hidden" name="springbot[action]" value="edit_options">';
            settings_fields( 'springbot_option_group' );
            do_settings_sections( 'springbot-sync-options' );
            echo '<h2>Springbot API Secret/Key</h2>';
            echo '<p>The Springbot sync process is currently using the WP_USER <b>' . SPRINGBOT_WP_USER . '</b>.<br> You can modify which user is being used under config/springbot_config.php<br>';
            echo '<input type="text" id="consumer-secret" name="springbot[consumer-secret]" value="' . $this->secret . '"  readonly="readonly" /><br>';
            echo '<input type="submit" id="re_create_auth" name="springbot[re-create]" value="Re-Create API Authorization"/><br>';
            submit_button();
            echo '</form>';
            echo '</div>';
            if (!empty($this->newKey)) {
                echo "<p>Your new consumer_key will be <b>" . $this->newKey . "</b></p>";
            }
        }

        /**
         * Show an the appropriate error message on failure
         */
        function my_error_notice() {
            if ( isset( $_GET['msg'] ) ) {
                if ( isset( $this->messages[ $_GET['msg'] ] ) ) {
                    $message = $this->messages[ $_GET['msg'] ];
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
            global $wpdb;

            $user = get_user_by( 'login', SPRINGBOT_WP_USER );
            if ( $user ) {
                $userId = $user->ID;
                $table = $wpdb->prefix . 'woocommerce_api_keys';
                $row = $wpdb->get_row( 'SELECT * from ' . $table . ' WHERE user_id = ' .  $userId . ';', ARRAY_A );
                $this->securityKey = get_user_meta( $userId, 'springbot_security_token', true );
                $this->guid = get_user_meta( $userId, 'springbot_store_guid', true );
                $this->storeId = get_user_meta( $userId, 'springbot_store_id', true );
                $this->secret = $row['consumer_secret'];
            } else {
                $this->securityKey = "Not Set Up Yet";
                $this->guid = "Not Set Up Yet";
                $this->storeId = "Not Set Up Yet";
                $this->secret = "Not Set Up Yet";
                $this->key = "Not Set Up Yet";
            }

            register_setting(
                'springbot_option_group',
                'springbot_option_name',
                array( $this, 'sanitize' )
            );

            add_settings_section(
                'setting_section_id',
                'Springbot Sync Options',
                array( $this, 'print_section_info' ),
                'springbot-sync-options'
            );

            add_settings_field(
                'springbot-guid',
                'Store GUID (Use the one with dashes)',
                array( $this, 'guid_callback' ),
                'springbot-sync-options',
                'setting_section_id'
            );

            add_settings_field(
                'springbot-store-id',
                'Store ID',
                array( $this, 'storeid_callback' ),
                'springbot-sync-options',
                'setting_section_id'
            );

            add_settings_field(
                'springbot-security-key',
                'Store Security Key (API Token)',
                array( $this, 'securitykey_callback' ),
                'springbot-sync-options',
                'setting_section_id'
            );
        }

        /**
         * Print the Section text
         */
        public function print_section_info() {
            echo 'This page allows you to edit your Springbot sync options. Please do NOT modify these settings unless instructed by springbot. This can break your springbot connection if modified improperly.';
        }

        /**
         * Get the settings option array and print one of its values
         */
        public function guid_callback() {
            echo '<input type="text" id="guid" name="springbot[guid]" value="' . $this->guid . '" />';
        }

        /**
         * Get the settings option array and print one of its values
         */
        public function storeid_callback() {
            echo '<input type="text" id="store-id" name="springbot[store-id]" value="' . $this->storeId . '" />';
        }

        /**
         * Get the settings option array and print one of its values
         */
        public function securitykey_callback() {
            echo '<input type="text" id="security-key" name="springbot[security-key]" value="' . $this->securityKey . '" />';
        }

    }

}
