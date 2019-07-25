<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

function remove_springbot_database_entries() {
    if ( $user = get_user_by( 'login', 'springbot' ) ) {
        delete_user_meta( $user->ID, 'springbot_security_token' );
        delete_user_meta( $user->ID, 'springbot_store_guid' );
        delete_user_meta( $user->ID, 'springbot_store_id' );
        wp_delete_user( $user->ID );
    }
}

function revoke_springbot_api_keys() {
    global $wpdb;
    $user_id = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users WHERE user_login = 'springbot'", ARRAY_A)['ID'];
    if (isset($user_id)) {
        $wpdb->delete($wpdb->prefix."woocommerce_api_keys", ['user_id' => $user_id], '%d');
    }
}

revoke_springbot_api_keys();
remove_springbot_database_entries();
