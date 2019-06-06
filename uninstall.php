<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if ( $user = get_user_by( 'login', 'springbot' ) ) {
	return wp_delete_user( $user->ID );
}
