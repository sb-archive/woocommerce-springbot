install:
	docker exec -it wordpress /bin/bash "/var/www/html/wordpress/wp-content/plugins/woocommerce-springbot/docker/check-install.sh"