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

remove_springbot_database_entries();
