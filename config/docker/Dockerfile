FROM wordpress:latest

# Install WooCommerce

RUN apt-get update
RUN apt-get install -y git-core mysql-client

RUN git clone https://github.com/woocommerce/woocommerce.git /usr/src/wordpress/wp-content/plugins/woocommerce

COPY . /usr/src/wordpress/wp-content/plugins/woocommerce-springbot
COPY config/docker/wp-config.php /usr/src/wordpress/wp-config.php

RUN curl -s https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/bin/wp
RUN chmod +x /usr/bin/wp
