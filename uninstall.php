<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$uninstall_url = SPRINGBOT_WOO_ETL . '/woocommerce/destroy';

function remove_springbot_plugin() {
    if ( is_admin() ) {

        remove_action( 'admin_menu', array( $GLOBALS["woocommerce_springbot"], 'springbot_menu_page' ) );
        remove_action( 'pre_user_query', array( $GLOBALS["woocommerce_springbot"], 'hide_springbot_api_user' ) );

    } else {

        if ( class_exists( 'Springbot_Footer' ) ) {
            remove_action( 'wp_footer', array( $springbot_footer, 'show_async_script' ) );
        }
        if ( class_exists( 'Springbot_Redirect' ) ) {
            remove_action( 'wp_loaded', array( $springbot_redirect, 'handle_redirect_posts' ) );
        }
        if ( class_exists( 'Springbot_Cart' ) ) {
            remove_action( 'woocommerce_cart_loaded_from_session', array(
                $springbot_cart,
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

// $response = wp_remote_post( $registration_url, array(
//     'method'      => 'POST',
//     'timeout'     => 45,
//     'redirection' => 5,
//     'httpversion' => '1.0',
//     'blocking'    => true,
//     'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
//     'body'        => json_encode( array(
//         'stores'              => array(
//             array(
//                 'local_store_id'         => 1,
//                 'instance_id'            => null,
//                 'guid'                   => $this->generate_guid(),
//                 'name'                   => get_bloginfo( 'name' ),
//                 'code'                   => $this->normalize( get_bloginfo( 'name' ) ),
//                 'url'                    => $store_url,
//                 'enabled'                => true,
//                 'secure_url'             => $store_url,
//                 'media_url'              => $store_url,
//                 'web_id'                 => 1,
//                 'store_mail_address'     => is_string( get_option( 'woocommerce_store_address' ) ) ? get_option( 'woocommerce_store_address' ) : '',
//                 'customer_service_email' => get_bloginfo( 'admin_email' ),
//                 'logo_url'               => '',
//                 'logo_alt_tag'           => get_bloginfo( 'name' ),
//                 'store_statuses'         => array(
//                     'pending-payment' => 'Pending Payment',
//                     'failed'          => 'Failed',
//                     'processing'      => 'Processing',
//                     'completed'       => 'Completed',
//                     'on-hold'         => 'On-Hold',
//                     'cancelled'       => 'Cancelled',
//                     'refunded'        => 'Refunded'
//                 )
//             )
//         ),
//         'platform'            => 'woocommerce',
//         'plugin_version'      => SPRINGBOT_PLUGIN_VERSION,
//         'woocommerce_version' => WC()->version,
//         'consumer_key'        => $consumer_key,
//         'consumer_secret'     => $consumer_secret,
//         'credentials'         => array(
//             'user_id'  => $email,
//             'password' => $password
//         )
//     ) ),
// ) );
