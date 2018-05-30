<?php

if ( ! class_exists( 'Springbot_Activation' ) ) {

	/**
	 * Main / front controller class
	 */
	class Springbot_Activation {

		/**
		 * Returns true if the instance has been registered with Springbot
		 */
		public function is_registered() {

		}

		/**
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {

		}

		/**
		 * @param string $email
		 * @param string $password
		 */
		public function register( $email, $password ) {
			$store_url = get_site_url();
			list( $consumer_key, $consumer_secret ) = $this->create_api_token();
			$registration_url = SPRINGBOT_WOO_ETL . '/api/v1/woocommerce/create';
			$response = wp_remote_post( $registration_url, array(
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
							'instance_id'            => null,
							'guid'                   => $this->generate_guid(),
							'name'                   => get_bloginfo( 'name' ),
							'code'                   => 'english-na',
							'url'                    => $store_url,
							'enabled'                => true,
							'secure_url'             => $store_url,
							'media_url'              => $store_url,
							'web_id'                 => 1,
							'store_mail_address'     => get_option( 'woocommerce_store_address' ),
							'customer_service_email' => get_bloginfo( 'admin_email' ),
							'logo_url'               => '',
							'logo_alt_tag'           => get_bloginfo( 'name' ),
							'store_statuses'         => array(
								'pending-payment' => 'Pending Payment',
								'failed'          => 'Failed',
								'processing'      => 'Processing',
								'completed'       => 'Completed',
								'on-hold'         => 'On-Hold',
								'cancelled'       => 'Cancelled',
								'refunded'        => 'Refunded'
							)
						)
					),
					'platform'        => 'woocommerce',
					'plugin_version'  => SPRINGBOT_PLUGIN_VERSION,
					'consumer_key'    => $consumer_key,
					'consumer_secret' => $consumer_secret,
					'credentials'     => array(
						'user_id'  => $email,
						'password' => $password
					)
				) ),
			) );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				error_log("Error during Springbot registration: {$error_message}");
				return 500;
			} else {
				return $response['response']['code'];
			}

		}


		/**
		 * Create an API token (key + secret) to send to the ETL
		 */
		private function create_api_token() {
			global $wpdb;

			$description = sprintf(
				__( '%1$s - API (created on %2$s at %3$s).', 'woocommerce' ),
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
