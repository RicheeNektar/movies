
DOCKER_COMPOSE_CMD=docker compose
DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE_CMD) exec web

.PHONY: default
default:
	@echo "run 'make start' to start docker"

.PHONY: update
update:
	@$(DOCKER_COMPOSE_CMD) pull

.PHONY: build
build:
	$(update)
	@$(DOCKER_COMPOSE_CMD) build

.PHONY: stop
stop:
	@$(DOCKER_COMPOSE_CMD) down

.PHONY: start
start:
	$(build)
	@$(DOCKER_COMPOSE_CMD) up -d

.PHONY: composer
composer:
	@$(DOCKER_COMPOSE_EXEC) php ./composer.phar install

.PHONY: composer-update
composer-update:
	@$(DOCKER_COMPOSE_EXEC) php ./composer.phar update

.PHONY: migrate
migrate:
	@$(DOCKER_COMPOSE_EXEC) php bin/console doctrine:migrations:migrate

.PHONY: console
console:
	@$(DOCKER_COMPOSE_EXEC) /bin/bash

.PHONY: yarn-install
yarn-install:
	@$(DOCKER_COMPOSE_EXEC) yarn install

.PHONY: yarn
yarn:
	@$(DOCKER_COMPOSE_EXEC) yarn dev
