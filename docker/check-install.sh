#!/bin/bash

if [ ! -f "/var/wp_installed" ]; then

    echo "installing woocommerce and springbot"

    wp core install \
        --allow-root \
        --url=localhost:8000 \
        --title="WooCommerce Test Site" \
        --admin_user=springbot \
        --admin_password=password \
        --admin_email=woocommerce@springbot.com \
        --path="/usr/src/wordpress"

    wp plugin activate woocommerce \
        --allow-root \
        --path="/var/www/html"

    wp plugin activate woocommerce-springbot \
        --allow-root \
        --path="/var/www/html"

    touch /var/wp_installed

else

	echo "woocommerce already installed"

fi
