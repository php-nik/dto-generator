IMAGE_COMPOSER = composer/composer
IMAGE_PHP = php:8.3-cli
DOCKER = docker
COMPOSER = composer
USER_ID = $(shell id -u)
USER_GROUP = $(shell id -g)
DOCKER_RUN_COMPOSER = $(DOCKER) run \
	-it \
	--rm \
	--user "$(USER_ID):$(USER_GROUP)" \
	--volume "$(PWD):/app" \
	--workdir "/app" \
	$(IMAGE_COMPOSER)

DOCKER_RUN_PHP = $(DOCKER) run \
	-it \
	--rm \
	--user "$(USER_ID):$(USER_GROUP)" \
	--volume "$(PWD):/app" \
	--workdir "/app" \
	$(IMAGE_PHP)

vendor: composer.json
	$(DOCKER_RUN_COMPOSER) composer install

shell-composer:
	$(DOCKER_RUN_COMPOSER) bash

phpunit: vendor
	$(DOCKER_RUN_PHP) php vendor/bin/phpunit

phpstan: vendor
	$(DOCKER_RUN_PHP) php vendor/bin/phpstan
