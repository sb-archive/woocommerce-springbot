FROM wordpress:latest

# this script is for testing and development purposes only

RUN apt-get update
RUN apt-get install -y git-core default-mysql-client sed

RUN git clone https://github.com/woocommerce/woocommerce.git /usr/src/wordpress/wp-content/plugins/woocommerce

COPY . /usr/src/wordpress/wp-content/plugins/woocommerce-springbot
COPY config/docker/wp-config.php /usr/src/wordpress/wp-config.php

RUN curl -s https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/bin/wp

RUN chmod +x /usr/bin/wp
RUN chmod +x /usr/src/wordpress/wp-content/plugins/woocommerce-springbot/config/docker/*.sh

# a way to get our install script to run inside of the official entrypoint file, we just manually insert at line 3
RUN sed -i '3i /usr/src/wordpress/wp-content/plugins/woocommerce-springbot/config/docker/docker-install.sh &' /usr/local/bin/docker-entrypoint.sh
