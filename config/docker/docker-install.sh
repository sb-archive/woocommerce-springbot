#!/bin/bash

# this script is for testing and development purposes only

if [ ! -f "/var/wp_installed" ]; then

    # wait until the official docker image is finished installing..
    while [ ! -f /var/www/html/wp-config.php ]; do
        echo "not installed yet..."
        sleep 1
    done

    echo "installing woocommerce and springbot"

    # wait for mysql server to become available
    while ! mysqladmin ping -h"mysql" --silent; do
        sleep 1
    done

    # create the database
    mysql -h mysql -u root -e "CREATE DATABASE wordpress;"
    mysql -h mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'springbot'@'%'; FLUSH PRIVILEGES;"

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

fi
