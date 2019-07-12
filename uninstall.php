<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

function remove_springbot_database_entries() {
    if ( is_admin() ) {

        remove_action( 'admin_menu', array( $GLOBALS["woocommerce_springbot"], 'springbot_menu_page' ) );
        remove_action( 'pre_user_query', array( $GLOBALS["woocommerce_springbot"], 'hide_springbot_api_user' ) );

    } else {

        if ( class_exists( 'Springbot_Footer' ) ) {
            remove_action( 'wp_footer', array( $GLOBALS['springbot_footer'], 'show_async_script' ) );
        }
        if ( class_exists( 'Springbot_Redirect' ) ) {
            remove_action( 'wp_loaded', array( $GLOBALS['springbot_redirect'], 'handle_redirect_posts' ) );
        }
        if ( class_exists( 'Springbot_Cart' ) ) {
            remove_action( 'woocommerce_cart_loaded_from_session', array(
                $GLOBALS['springbot_cart'],
                'handle_cart_endpoint'
            ) );
        }
    }

    if ( $user = get_user_by( 'login', 'springbot' ) ) {
        delete_user_meta( $user->ID, 'springbot_security_token' );
        delete_user_meta( $user->ID, 'springbot_store_guid' );
        delete_user_meta( $user->ID, 'springbot_store_id' );
        wp_delete_user( $user->ID );
    }
}

public function remove_springbot_etl_entry() {
    $store_id = -1;
    if ( $user = get_user_by( 'login', 'springbot' ) ) {
        $store_id = get_user_meta($user->ID, 'springbot_store_id', true);
    }

    $uninstall_url = SPRINGBOT_WOO_ETL . '/woocommerce/destroy';
    $response = wp_remote_post( $uninstall_url, array(
        'method'      => 'POST',
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => false,
        'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
        'body'        => json_encode( {
                'store_id' => $store_id
            }
        ) ),
    ) ;

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        error_log( "Error during Springbot uninstall: {$error_message}" );

        return 500;
    } else {
        return $response['response']['code'];
    }
}

remove_springbot_database_entries();
