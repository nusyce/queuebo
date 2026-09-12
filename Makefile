ENV_FILE ?= .env.docker
DC        = docker compose --env-file $(ENV_FILE)

.DEFAULT_GOAL := help

help: ## List targets
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
	 awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-14s\033[0m %s\n",$$1,$$2}'

init: hooks ## Create .env.docker from the example
	@test -f $(ENV_FILE) || cp .env.docker.example $(ENV_FILE)
	@echo "edit $(ENV_FILE), then: make up"

hooks: ## Wire the repo's git hooks (warns before push if app/docker/dokploy files are uncommitted)
	@git config core.hooksPath .githooks
	@echo "git hooks enabled (core.hooksPath=.githooks)"

build: ## Build the image
	$(DC) build

up: ## Start the stack
	$(DC) up -d --build

down: ## Stop the stack (keep volumes)
	$(DC) down

destroy: ## Stop and delete db + storage volumes
	$(DC) down -v

logs: ## Tail application logs
	$(DC) logs -f app queue scheduler

sh: ## Shell into the app container
	$(DC) exec app bash

key: ## Generate APP_KEY inside the container
	$(DC) exec app php artisan key:generate --force

migrate: ## Run migrations
	$(DC) exec app php artisan migrate --force

about: ## artisan about
	$(DC) exec app php artisan about

backup: ## Dump the database to backup.sql
	$(DC) exec -T mysql sh -c 'exec mysqldump -u$$MYSQL_USER -p$$MYSQL_PASSWORD $$MYSQL_DATABASE' > backup.sql

.PHONY: help init hooks build up down destroy logs sh key migrate about backup
