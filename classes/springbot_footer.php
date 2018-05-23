<?php

if ( ! class_exists( 'Springbot_Footer' ) ) {

	class Springbot_Footer {

		/**
		 * Show the async script only if the GUID is set
		 */
		public function show_async_script() {
			if ($guid = $this->get_guid()) {
				echo "<script>alert('GUID is {$guid}');</script>";
			}
		}

		/**
		 * Get the GUID from the springbot user
		 */
		private function get_guid()
		{
			if ($user = get_user_by( 'user_login', 'springbot')) {
				return get_user_meta($user->ID, 'springbot_store_guid');
			}
			return null;
		}

	}

}