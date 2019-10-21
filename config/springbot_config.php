<?php

define( 'SPRINGBOT_PLUGIN_VERSION', '0.0.13.400' );

define( 'SPRINGBOT_NAME', 'WooCommerce Springbot Integration' );
define( 'SPRINGBOT_REQUIRED_PHP_VERSION', '5.3' );
define( 'SPRINGBOT_REQUIRED_WP_VERSION', '3.1' );

define( 'SPRINGBOT_WOO_ETL',
	getenv( 'SPRINGBOT_WOO_ETL' ) ? getenv( 'SPRINGBOT_WOO_ETL' ) : 'https://etl.springbot.com'
);

define( 'SPRINGBOT_ASSETS_DOMAIN',
	getenv( 'SPRINGBOT_ASSETS_DOMAIN' ) ? getenv( 'SPRINGBOT_ASSETS_DOMAIN' ) : 'd2z0bn1jv8xwtk.cloudfront.net'
);

define( 'SPRINGBOT_APP_URL',
	getenv( 'SPRINGBOT_APP_URL' ) ? getenv( 'SPRINGBOT_APP_URL' ) : 'https://app.springbot.com'
);

define( 'SPRINGBOT_WP_USER',
	getenv( 'SPRINGBOT_WP_USER' ) ? getenv( 'SPRINGBOT_WP_USER' ) : 'springbot'
);