<?php

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have permission to access this page.' ) );
}

echo '<div class="wrap">';
echo '<p>Springbot credentials sync form here.</p>';
echo '</div>';