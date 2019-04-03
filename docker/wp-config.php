<?php

define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wordpress' );
define( 'DB_PASSWORD', 'wordpress' );
define( 'DB_HOST', 'woo_store_db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'ox3XL=t)rHqsHb$)|M8`7Dnt`V*YNDIS}<kHse(;Ml]zXKL&wy6W[%cZp6A3fq56');
define( 'SECURE_AUTH_KEY',  '$ZL<QuKc`Mf{Ad4|*CQ7Ru#`Eiu?vnJ,4}+!p5xOQBD zg3T}Yv$1&9WBAaKFW}F');
define( 'LOGGED_IN_KEY',    'GoE1f3W-F,VHIYst}JxcryO/f0[ORwJsrqc]PK2qqWT(W*=Pu/4#>4yJ-}>FYH@E');
define( 'NONCE_KEY',        '=:KcNm?V5-8B)=7vwoqU9+dcqxF:MOd/]n9pMrqWWP1g@OUax`{0N8|T1...*.%:');
define( 'AUTH_SALT',        'KXG9m%49/P^kT(b.s 3y|l~B8A u;Br=5sExAi!!:sUb< e:0pS}D?94dg,km,6 ');
define( 'SECURE_AUTH_SALT', 'HAq@GNd&(@{Zx%qMf;NeN: _vQG-1PLT~.L!I9{Z#H2=z)$rx8YQ|W^@T=H8juk7');
define( 'LOGGED_IN_SALT',   '2mxRLUVNy_O+IYup47ccx<m}Y#/ RA0x1jgUbi.RiRL?0+xR#mA.a>oU$DvU 2c&');
define( 'NONCE_SALT',       'mSPJ<t[B>IYR:>VMJwHSK+[(_NaM3C4qc.wwRzcIu|A<7utz09NR#&IQ]IO5InRZ');

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

require_once( ABSPATH . 'wp-settings.php' );