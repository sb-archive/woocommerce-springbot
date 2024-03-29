<?php

define( 'SPRINGBOT_PLUGIN_VERSION', '1.0.4' );

define( 'SPRINGBOT_NAME', 'Springbot' );
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

define( 'SPRINGBOT_WP_EMAIL',
	getenv( 'SPRINGBOT_WP_EMAIL' ) ? getenv( 'SPRINGBOT_WP_EMAIL' ) : 'woocommerce@springbot.com'
);