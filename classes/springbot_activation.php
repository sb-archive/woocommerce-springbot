<?php

if ( ! class_exists( 'Springbot_Activation' ) ) {

	/**
	 * Main / front controller class
	 */
	class Springbot_Activation {

		/**
		 * Returns true if the instance has been registered with Springbot
		 *
		 * @return bool
		 */
		public function is_registered() {
			if ( $user = get_user_by( 'login', 'springbot' ) ) {
				return (bool) get_user_meta( $user->ID, 'springbot_store_id' );
			}

			return false;
		}

		/**
		 * Returns the springbot store ID if it exists, null otherwise
		 *
		 * @return int|null
		 */
		public function get_springbot_store_id() {
			if ( $user = get_user_by( 'login', 'springbot' ) ) {
				return get_user_meta( $user->ID, 'springbot_store_id', true );
			}

			return null;
		}

		/**
		 * @param string $email
		 * @param string $password
		 *
		 * @return int
		 */
		public function register( $email, $password ) {
			$store_url = get_site_url();
			list( $consumer_key, $consumer_secret ) = $this->create_api_token();
			$registration_url = SPRINGBOT_WOO_ETL . '/woocommerce/create';

			$response = wp_remote_post( $registration_url, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'        => json_encode( array(
					'stores'              => array(
						array(
							'local_store_id'         => 1,
							'instance_id'            => null,
							'guid'                   => $this->generate_guid(),
							'name'                   => get_bloginfo( 'name' ),
							'code'                   => $this->normalize( get_bloginfo( 'name' ) ),
							'url'                    => $store_url,
							'enabled'                => true,
							'secure_url'             => $store_url,
							'media_url'              => $store_url,
							'web_id'                 => 1,
							'store_mail_address'     => is_string( get_option( 'woocommerce_store_address' ) ) ? get_option( 'woocommerce_store_address' ) : '',
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
					'platform'            => 'woocommerce',
					'plugin_version'      => SPRINGBOT_PLUGIN_VERSION,
					'woocommerce_version' => WC()->version,
					'consumer_key'        => $consumer_key,
					'consumer_secret'     => $consumer_secret,
					'credentials'         => array(
						'user_id'  => $email,
						'password' => $password
					)
				) ),
			) );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				error_log( "Error during Springbot registration: {$error_message}" );

				return 500;
			} else {
				$decoded = json_decode( $response['body'], true );
				foreach ( $decoded['stores'] as $guid => $store ) {
					$this->save_springbot_data( $decoded['security_token'], $guid, $store['springbot_store_id'] );
				}

				return $response['response']['code'];
			}

		}

		/**
		 * @param $securityToken
		 * @param $guid
		 * @param $springbotStoreId
		 *
		 * @return bool
		 */
		private function save_springbot_data( $securityToken, $guid, $springbotStoreId ) {
			if ( $user = get_user_by( 'login', 'springbot' ) ) {
				update_user_meta( $user->ID, 'springbot_security_token', $securityToken );
				update_user_meta( $user->ID, 'springbot_store_guid', $guid );
				update_user_meta( $user->ID, 'springbot_store_id', $springbotStoreId );

				return true;
			}

			return false;
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

			$user = get_user_by( 'login', 'springbot' );
			if ( $user ) {
				if ( ! in_array( 'administrator', $user->roles ) ) {
					$user->set_role( 'administrator' );
				}
				$userId = $user->ID;
			} else {
				$userId = wp_insert_user( array(
					'user_login' => 'springbot',
					'user_pass'  => $this->random_password(),
					'user_email' => 'woocommerce@springbot.com',
					'role'       => 'administrator'
				) );
			}

			// Created API keys.
			$consumer_key    = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();

			$wpdb->insert(
				$wpdb->prefix . 'woocommerce_api_keys',
				array(
					'user_id'         => $userId,
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

		/**
		 * Generate a random password for our springbot user
		 *
		 * @param int $length
		 *
		 * @return string
		 */
		private function random_password( $length = 12 ) {
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$count = mb_strlen( $chars );

			for ( $i = 0, $result = ''; $i < $length; $i ++ ) {
				$index  = rand( 0, $count - 1 );
				$result .= mb_substr( $chars, $index, 1 );
			}

			return $result;
		}

		/**
		 * Normalize a string by removing spaces and converting to lowercase
		 *
		 * @param $string
		 *
		 * @return string
		 */
		private function normalize( $string ) {
			return strtolower( str_replace( ' ', '-', $string ) );
		}

	}

}
