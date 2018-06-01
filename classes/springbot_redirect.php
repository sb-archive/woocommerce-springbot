<?php

if ( ! class_exists( 'Springbot_Redirect' ) ) {

	class Springbot_Redirect {

		public function __construct() {
			add_action( 'wp_loaded', array( $this, 'handle_redirect_posts' ) );
		}

		/**
		 * Check if the path is /i, and if so redirect to the Springbot instagram page
		 *
		 * @param $permalink
		 *
		 * @return mixed
		 */
		public function handle_redirect_posts( $permalink ) {
			$uri   = $_SERVER['REQUEST_URI'];
			$parts = parse_url( $uri );
			$path  = trim( str_replace( 'index.php', '', $parts['path'] ), '/' );

			if ( $path === 'i' ) {
				if ( $user = get_user_by( 'login', 'springbot' ) ) {
					$meta = get_user_meta( $user->ID );
					if ( isset( $meta['springbot_store_id'] ) && is_numeric( $meta['springbot_store_id'][0] ) ) {
						wp_redirect( SPRINGBOT_APP_URL . "/i/{$meta['springbot_store_id'][0]}", 301 );
						exit;
					}
				}
			}

			return $permalink;
		}

	}

}
