<?php

if ( ! class_exists( 'Springbot_Footer' ) ) {

	class Springbot_Footer {

		/**
		 * Show the async script only if the GUID is set
		 */
		public function show_async_script() {
			if ($guid = $this->get_guid()) {
				
				echo "<script type=\"text/javascript\">\n";
				echo "    var _sbparams = _sbparams || [];\n";
				echo "    (function () {\n";
				echo "	    var sb = document.createElement('script');\n";
				echo "	    var fs = document.getElementsByTagName('script')[0];\n";
				echo "	    sb.type = 'text/javascript';\n";
				echo "	    sb.async = true;\n";
				echo "	    sb.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + " . SPRINGBOT_ASSETS_DOMAIN . "/async/preload/{$guid}.js\n";
				echo "	    fs.parentNode.insertBefore(sb, fs);\n";
				echo "    })();\n";
				echo "  </script>\n";
				
			}
		}

		/**
		 * Get the GUID from the springbot user
		 */
		private function get_guid()
		{
			if ($user = get_user_by( 'login', 'springbot')) {
				$guid = get_user_meta($user->ID, 'springbot_store_guid', true);
				$guid = strtolower($guid);
				$guid = str_replace('-', '', $guid);
				return $guid;
			}
			return null;
		}

	}

}
