<?php

if ( ! class_exists( 'Springbot_Activation' ) ) {

	/**
	 * Main / front controller class
	 */
	class Springbot_Activation {

		/**
		 * Returns true if the instance has been registered with Springbot
		 */
		public function is_registered()
		{

		}

		/**
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			$this->create_api_token();

			$storeUrl = get_permalink( woocommerce_get_page_id( 'shop' ) );
			list( $consumer_key, $consumer_secret ) = $this->create_api_token();
			wp_remote_post( SPRINGBOT_WOO_ETL, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'        => json_encode( array(
					'stores'          => array(
						array(
							'local_store_id'         => 1,
							'instance_id'            => 1,
							'guid'                   => $this->generate_guid(),
							'name'                   => get_bloginfo( 'name' ),
							'code'                   => 'english-na',
							'url'                    => $storeUrl,
							'enabled'                => true,
							'secure_url'             => $storeUrl,
							'media_url'              => $storeUrl,
							'web_id'                 => 1,
							'store_mail_address'     => '123 Fake St',
							'customer_service_email' => get_bloginfo( 'admin_email' ),
							'logo_url'               => '',
							'logo_alt_tag'           => 'Woo store logo',
							'store_statuses'         => array(
								'canceled' => 'Canceled',
								'complete' => 'Complete',
								'fraud'    => 'Fraud'
							)
						)
					),
					'platform'        => 'woocommerce',
					'plugin_version'  => '1.0.0.0',
					'consumer_key'    => $consumer_key,
					'consumer_secret' => $consumer_secret,
					'credentials'     => array(
						'user_id'  => 'dev@springbot.com',
						'password' => 'password'
					)
				) ),
			) );
		}

		private function create_api_token() {
			global $wpdb;

			$description = sprintf(
				__( '%1$s - API %2$s (created on %3$s at %4$s).', 'woocommerce' ),
				wc_clean( 'Springbot' ),
				date_i18n( wc_date_format() ),
				date_i18n( wc_time_format() )
			);
			$user        = wp_get_current_user();

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
					'truncated_key'   => substr( $consumer_key, - 7 ),
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s' )
			);

			return [ $consumer_key, $consumer_secret ];
		}

		/**
		 * Generate a semi-unique "GUID" so that if a store gets re-registered we can re-associate it
		 */
		private function generate_guid() {
			$hash = sha1( get_bloginfo( 'name' ) . get_bloginfo( 'url' ) . get_bloginfo( 'admin_email' ) );

			return substr( $hash, 0, 8 ) . '-'
			       . substr( $hash, 8, 4 ) . '-'
			       . substr( $hash, 12, 4 ) . '-'
			       . substr( $hash, 16, 4 ) . '-'
			       . substr( $hash, 20, 12 );
		}

	}

}
