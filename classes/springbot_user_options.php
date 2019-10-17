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

        /**
         * Springbot_Options constructor.
         *
         * @param Springbot_Activation $activation
         */
        public function __construct( Springbot_Activation $activation ) {
            global $wpdb;

            add_action( 'admin_init', array( $this, 'page_init' ) );
            add_action( 'admin_notices', array( $this, 'my_error_notice' ) );

            $user = get_user_by( 'login', SPRINGBOT_WP_USER );
            if ( !$user ) {
                $redirect = 'admin.php';
                $redirect = add_query_arg( 'msg', 401, $redirect );
                $redirect = add_query_arg( 'page', 'springbot', $redirect );
                wp_redirect( $redirect );
                exit;
            }

            if ( isset( $_POST['springbot']['action'] ) ) {
                if ( ! current_user_can( 'activate_plugins' ) ) {
                    $redirect = 'admin.php';
                    $redirect = add_query_arg( 'msg', 'not_admin', $redirect );
                    $redirect = add_query_arg( 'page', 'springbot', $redirect );
                    wp_redirect( $redirect );
                    exit;
                } else {
                    $code = $this->save_springbot_data( $_POST['springbot']['store-id'], $_POST['springbot']['guid'], $_POST['springbot']['security-key'] );
                    if ( !$code ) {
                        $redirect = 'admin.php';
                        $redirect = add_query_arg( 'msg', 401, $redirect );
                        $redirect = add_query_arg( 'page', 'springbot', $redirect );
                        wp_redirect( $redirect );
                        exit;
                    }
                }
            }
            
            $userId = $user->ID;
            $table = $wpdb->prefix . 'woocommerce_api_keys';
            $row = $wpdb->get_row( 'SELECT * from ' . $table . ' WHERE user_id = ' .  $userId . ';', ARRAY_A );
            $this->securityKey = get_user_meta( $userId, 'springbot_security_token', true );
            $this->guid = get_user_meta( $userId, 'springbot_store_guid', true );
            $this->storeId = get_user_meta( $userId, 'springbot_store_id', true );
            $this->secret = $row['consumer_secret'];
            $this->key = $row['consumer_key'];
        }

        /**
         * @param $securityToken
         * @param $guid
         * @param $springbotStoreId
         *
         * @return bool
         */
        private function save_springbot_data( $securityToken, $guid, $springbotStoreId ) {
            if ( $user = get_user_by( 'login', SPRINGBOT_WP_USER ) ) {
                update_user_meta( $user->ID, 'springbot_security_token', $securityToken );
                update_user_meta( $user->ID, 'springbot_store_guid', $guid );
                update_user_meta( $user->ID, 'springbot_store_id', $springbotStoreId );

                return true;
            }

            return false;
        }

        /**
         * Options page callback
         */
        public function create_admin_page() {

            $activation = new Springbot_Activation();
            echo '<div class="wrap">';
            echo '<h1>Springbot Options</h1>';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin.php' ) ) . '?page=springbot-options">';
            echo '<input type="hidden" name="springbot[action]" value="edit_options">';
            settings_fields( 'springbot_option_group' );
            do_settings_sections( 'springbot-sync-options' );
            submit_button();
            echo '</form>';
            echo '<h2>Springbot API Secret/Key</h2>';
            echo '<p>The Springbot sync process is currently using the WP_USER <b>' . SPRINGBOT_WP_USER . '</b>.<br> You can modify which user is being used under config/springbot_config.php<br>';
            echo '<input type="text" id="consumer-secret" name="springbot[consumer-secret]" value="' . $this->secret . '"  readonly="readonly" /><br>';
            echo '<input type="text" id="consumer-key" name="springbot[consumer-key]" value="' . $this->key . '"  readonly="readonly" /><br>';
            echo '</div>';
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
                'Store GUID',
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
                'Store Security Key',
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
