<?php 
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// get springbot defaults
require_once( __DIR__ . '/config/springbot_config.php' );

// delete the springbot user based on springbot default user
if ( $user = get_user_by( 'login', SPRINGBOT_WP_USER ) ) {
	wp_delete_user($user->ID);
}

