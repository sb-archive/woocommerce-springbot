<?php

if ( ! class_exists( 'WooCommerce_Springbot_Integration' ) ) {

	/**
	 * Main / front controller class
	 */
	class WooCommerce_Springbot_Integration {

		const PREFIX = 'wsi_';
		const DEBUG_MODE = false;

		private static $instance;


		/**
		 * Constructor
		 */
		protected function __construct() {
			self::$instance = $this;
		}

		/**
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			$this->create_api_token();
			/*
			wp_remote_post( 'https://9580bfc9.ngrok.io', array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'        => json_encode( array(
					'foo' => 'bar'
				) ),
			) );
			*/
		}

		private function create_api_token()
		{
			global $wpdb;

			/* translators: 1: app name 2: scope 3: date 4: time */
			$description = sprintf(
				__( '%1$s - API %2$s (created on %3$s at %4$s).', 'woocommerce' ),
				wc_clean( 'Springbot' ),
				date_i18n( wc_date_format() ),
				date_i18n( wc_time_format() )
			);
			$user = wp_get_current_user();

			// Created API keys.
			$consumer_key    = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();

			$wpdb->insert(
				$wpdb->prefix . 'woocommerce_api_keys',
				array(
					'user_id'         => $user->ID,
					'description'     => $description,
					'permissions'     => 'read_write',
					'consumer_key'    => wc_api_hash( $consumer_key ),
					'consumer_secret' => $consumer_secret,
					'truncated_key'   => substr( $consumer_key, -7 ),
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		}

		public static function get_instance() {
			if ( isset( self::$instance ) ) {
				return self::$instance;
			}

			return new WooCommerce_Springbot_Integration();
		}

	}

}
