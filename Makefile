.PHONY: help install assets build dev migrate fixtures db-test test coverage test-e2e test-all lint stan cs-check cs-fix clean

SERVER_HOST := 127.0.0.1
SERVER_PORT := 8098
PID_FILE    := .test-server.pid
CS_FLAGS    := --allow-risky=yes --allow-unsupported-php-version=yes

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

install: ## Install PHP and JS dependencies
	composer install
	npm install

assets: build ## Alias for build

build: ## Build production assets (Vite)
	npm run build

dev: ## Start the Vite dev server (run alongside the app server)
	npm run dev

server: ## Start the local dev app server
	php -S $(SERVER_HOST):8000 -t public

migrate: ## Run Doctrine migrations (dev env)
	php bin/console doctrine:migrations:migrate -n

fixtures: ## Load Doctrine fixtures (dev env)
	php bin/console doctrine:fixtures:load -n

db-test: ## Reset the test database to an empty schema
	php bin/console doctrine:database:create --if-not-exists -e test -n
	php bin/console doctrine:schema:drop --force -e test -n || true
	php bin/console doctrine:schema:create -e test -n

test: ## Run the PHPUnit functional suite (test env)
	APP_ENV=test php bin/phpunit

coverage: ## Run PHPUnit coverage and write var/coverage.xml
	XDEBUG_MODE=coverage APP_ENV=test php bin/phpunit --coverage-text --coverage-clover var/coverage.xml

test-e2e: build ## Run the Cypress E2E suite (headless) against the test server
	@trap 'kill $$(cat $(PID_FILE)) 2>/dev/null || true' EXIT; \
	php -S $(SERVER_HOST):$(SERVER_PORT) -t public public/index_test.php > /tmp/cti-server.log 2>&1 & echo $$! > $(PID_FILE); \
	sleep 2; \
	npx cypress run --browser electron

test-e2e-open: build ## Run Cypress interactively (GUI) with a managed test server
	@trap 'kill $$(cat $(PID_FILE)) 2>/dev/null || true' EXIT; \
	php -S $(SERVER_HOST):$(SERVER_PORT) -t public public/index_test.php > /tmp/cti-server.log 2>&1 & echo $$! > $(PID_FILE); \
	sleep 2; \
	npx cypress open

test-server: ## Start the local app server in the TEST environment (for cypress open)
	php -S $(SERVER_HOST):$(SERVER_PORT) -t public public/index_test.php

test-all: test test-e2e ## Run functional + E2E suites

stan: ## Run PHPStan static analysis (level 6)
	php bin/console cache:warmup -e dev -n >/dev/null
	vendor/bin/phpstan analyse --no-progress --ansi

cs-check: ## Check coding standards without changing files
	vendor/bin/php-cs-fixer fix --dry-run --diff $(CS_FLAGS)

cs-fix: ## Apply coding standard fixes
	vendor/bin/php-cs-fixer fix $(CS_FLAGS)

lint: stan cs-check ## Run static analysis, style check, Twig/container lints, and schema validation
	php bin/console lint:twig templates -n
	php bin/console lint:container -n
	php bin/console doctrine:schema:validate -n

clean: ## Clear all Symfony caches
	rm -rf var/cache/*
