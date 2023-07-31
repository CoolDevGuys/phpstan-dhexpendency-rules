current-dir := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))
SHELL = /bin/sh
user-id := $(shell id -u)
group-id := $(shell id -g)

composer-install:
	@echo "[INFO] Installing dependencies 📚"
	@docker run --rm --volume $(current-dir):/app --user $(id -u):www-data \
		 composer:2.5 composer install \
			--prefer-dist --optimize-autoloader --no-ansi --no-interaction --prefer-dist --no-progress

tests: prerequisites
	vendor/bin/phpunit

.PHONY: prerequisites
prerequisites: vendor/autoload.php composer.lock

docker-run:
	@docker run --rm --volume $(current-dir):/app --user $(id -u):www-data \
	php:7.4.33-cli-alpine /bin/sh -c "cd /app && vendor/bin/phpunit -c phpunit.xml.dist"