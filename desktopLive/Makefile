DOCKER_COMPOSE_FILE = compose.yaml

COMPOSE_PROJECT_NAME = liveshopping

DOCKER_COMPOSE = docker compose -p $(COMPOSE_PROJECT_NAME) -f $(DOCKER_COMPOSE_FILE)

DOCKER_COMPOSE_EXEC	   = $(DOCKER_COMPOSE) exec -T

DOCKER_COMPOSE_UP	   = $(DOCKER_COMPOSE) up -d --build --remove-orphans --no-recreate

EXEC_PHP	   = $(DOCKER_COMPOSE_EXEC) php

EXEC_DB		   = $(DOCKER_COMPOSE) exec database

APP_ENV        = dev

SYMFONY_NO_ENV = $(EXEC_PHP) php bin/console

SYMFONY		   = $(SYMFONY_NO_ENV) --env=${APP_ENV}

COMPOSER	   = $(EXEC_PHP) composer

NPM      	   = $(EXEC_PHP) npm

PHPUNIT		   = $(EXEC_PHP) bin/phpunit

PWD            = $(shell pwd)

PHP_CS_FIXER   = $(EXEC_PHP) ./vendor/bin/php-cs-fixer

PHP_CS_LOCAL   = ./vendor/bin/php-cs-fixer

warn = /bin/echo -e "\x1b[33m⚠️$1 ⚠\x1b[0m"

inform = /bin/echo -e "\x1b[34m$1\x1b[0m"

##

## Project

## -------

##

start: up wait-for-postgres install ## Start project (build, up, wait for DB, install dependencies)

	@$(call inform,"✅ Projet démarré avec succès!")
	@$(call inform,"🌐 Application accessible sur: http://localhost:8000")
	@echo ""
	@echo "Commandes utiles:"
	@echo "  make logs        # Voir les logs"
	@echo "  make shell       # Accéder au conteneur PHP"
	@echo "  make down        # Arrêter les conteneurs"
	@echo "  make help        # Voir toutes les commandes"

install: vendor wait-for-postgres cache-warmup assets schema_update fix-permissions ## Install everything

up: ## Start Docker containers

	@$(call inform,"📦 Construction et démarrage des conteneurs...")
	$(DOCKER_COMPOSE_UP)

down: ## Stop Docker containers

	$(DOCKER_COMPOSE) down

logs: ## Show Docker containers logs

	$(DOCKER_COMPOSE) logs -f

shell: ## Enter in PHP container

	$(DOCKER_COMPOSE) exec php sh

shell-db: ## Enter in database container

	$(DOCKER_COMPOSE) exec database bash

wait-for-postgres:
	@$(call inform,"Waiting for PostgreSQL to be ready...")
	@until $(DOCKER_COMPOSE) exec -T database pg_isready -U app -d app > /dev/null 2>&1; do \
		echo "Retry..."; \
		sleep 2; \
	done
	@$(call inform,"✅ PostgreSQL is ready!")

hard-reset:

	$(EXEC_PHP) rm -rf vendor node_modules var/cache/* public/bundles public/js public/build var/logs

##

## File dependencies

## -----------------

##

vendor: fix-permissions composer-install-no-scripts npm-install

composer-update: ## Update Composer dependencies

	$(COMPOSER) update $(filter-out $@,$(MAKECMDGOALS))

composer-req: ## require a composer dependency

	$(COMPOSER) require $(filter-out $@,$(MAKECMDGOALS))

# eg:

# make composer-req my-package

composer-req-dev: ## require a composer dependency

	$(COMPOSER) require --dev $(filter-out $@,$(MAKECMDGOALS))

# eg:

# make composer-req-dev my-package

composer-install: ## Install Composer dependencies

	$(COMPOSER) install --no-progress

composer-install-no-scripts: ## Install Composer dependencies without running scripts

	$(COMPOSER) install --no-progress --no-scripts

npm-install:

	$(NPM) install --no-progress --no-audit

##

## Utils

## -----

##

sf: ## run a sf command

	$(SYMFONY) $(filter-out $@,$(MAKECMDGOALS))

fix-permissions: ## Fix file permissions

	$(EXEC_PHP) mkdir -p var/cache var/log

	$(EXEC_PHP) chmod -R 777 var/cache var/log

assets: ## Build assets

	@$(call inform,"Copying static assets.")

	@$(SYMFONY) assets:install -q public

js_dump: ## Dump js files (if using FOSJsRoutingBundle)

	@$(call inform,"Dumping JS routes and translations.")

	@$(SYMFONY) fos:js-routing:dump --target=public/js/routes_${APP_ENV}.js || true

	@$(SYMFONY) bazinga:js-translation:dump public/js || true

cache-warmup: ## Warmup cache

	$(SYMFONY) cache:warmup

cache-clear: ## Clear caches

	$(SYMFONY) cache:clear

##

## Database

## -----

##

schema_update: composer-install-no-scripts ## simple schema update

	$(SYMFONY) doctrine:schema:update --force

migration-diff: composer-install-no-scripts ## Generate a new doctrine migration

	$(SYMFONY) doctrine:migrations:diff

migrate: composer-install-no-scripts ## Apply database migrations

	$(SYMFONY) doctrine:migrations:migrate --no-interaction

psql:

	$(EXEC_DB) psql -U app -d app

##

## Tests

## -----

##

phpunit:

	$(PHPUNIT)

PHP_CS_ARGS=--diff

php-cs-fixer: ## Run PHP-CS fixer

	$(PHP_CS_FIXER) fix -vvv --dry-run --diff

php-cs-fixer-fix: ## Run PHP-CS fixer

	$(PHP_CS_FIXER) fix

phpcs: ## Run PHP-CS fixer

	$(PHP_CS_FIXER) fix -vvv --dry-run --diff

phpcs-fix: ## Run PHP-CS fixer

	$(PHP_CS_FIXER) fix

phpcs-local-fix: ## Run PHP-CS fixer locally

	$(PHP_CS_LOCAL) fix

doctrine-schema-validate: ## Run doctrine:schema:validate

	$(SYMFONY) doctrine:schema:validate

lint-twig:

	$(SYMFONY) lint:twig templates --env=prod

lint-yaml:

	$(SYMFONY) lint:yaml config

	$(SYMFONY) lint:yaml src

lint: doctrine-schema-validate lint-twig lint-yaml

.DEFAULT_GOAL := help

help:

	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

.PHONY: help

