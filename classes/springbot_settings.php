<?php

if ( ! class_exists( 'Springbot_Settings' ) ) {

	class Springbot_Settings {

		public static function add_settings_tab( $settings_tabs ) {
			$settings_tabs['settings_tab_springbot'] = __( 'Springbot', 'woocommerce-settings-tab-springbot' );
			return $settings_tabs;
		}

		public static function settings_tab() {
			woocommerce_admin_fields( self::get_settings() );
		}

		public static function update_settings() {
			woocommerce_update_options( self::get_settings() );
		}

		public static function get_settings() {
			$settings = array(
				'springbot_section_title' => array(
					'name'     => __( 'Springbot Settings', 'woocommerce-settings-tab-springbot' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wc_settings_tab_springbot_section_title'
				),
				'springbot_checkout_subscribe' => array(
					'name' => __( 'Checkout Subscribe', 'woocommerce-settings-tab-springbot' ),
					'type' => 'checkbox',
					'desc' => __( 'Enable subscribe checkbox on checkout', 'woocommerce-settings-tab-springbot' ),
					'id'   => 'wc_settings_tab_springbot_subscribe_checkbox'
				),
			);
			return apply_filters( 'wc_settings_tab_springbot_settings', $settings );
		}

	}

}
