.PHONY: help setup install jwt-generate db-create db-migrate db-reset test cache-clear serve

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
RED := \033[0;31m
NC := \033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)User Roles API - Available Commands$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

setup: ## Complete setup (install, jwt, env, db)
	@echo "$(BLUE)Starting complete setup...$(NC)"
	@$(MAKE) install
	@$(MAKE) env-setup
	@$(MAKE) jwt-generate
	@echo "$(GREEN)✓ Setup complete!$(NC)"
	@echo "$(YELLOW)Next steps:$(NC)"
	@echo "  1. Edit .env with your database credentials"
	@echo "  2. Run: make db-create"
	@echo "  3. Run: make db-migrate"
	@echo "  4. Run: make serve"

install: ## Install composer dependencies
	@echo "$(BLUE)Installing dependencies...$(NC)"
	composer install
	@echo "$(GREEN)✓ Dependencies installed$(NC)"

env-setup: ## Create .env from .env.example if not exists
	@if [ ! -f .env ]; then \
		echo "$(BLUE)Creating .env file...$(NC)"; \
		cp .env.example .env; \
		echo "$(GREEN)✓ .env file created$(NC)"; \
		echo "$(YELLOW)⚠ Please edit .env with your configuration$(NC)"; \
	else \
		echo "$(YELLOW).env file already exists, skipping...$(NC)"; \
	fi

jwt-generate: ## Generate JWT public/private keys
	@if [ ! -d config/jwt ]; then \
		echo "$(BLUE)Generating JWT keys...$(NC)"; \
		mkdir -p config/jwt; \
		php bin/console lexik:jwt:generate-keypair --skip-if-exists; \
		echo "$(GREEN)✓ JWT keys generated in config/jwt/$(NC)"; \
	else \
		echo "$(YELLOW)JWT directory exists, regenerating keys...$(NC)"; \
		php bin/console lexik:jwt:generate-keypair --overwrite; \
		echo "$(GREEN)✓ JWT keys regenerated$(NC)"; \
	fi

db-create: ## Create the database
	@echo "$(BLUE)Creating database...$(NC)"
	php bin/console doctrine:database:create --if-not-exists
	@echo "$(GREEN)✓ Database created$(NC)"

db-migrate: ## Run database migrations
	@echo "$(BLUE)Running migrations...$(NC)"
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "$(GREEN)✓ Migrations completed$(NC)"

db-reset: ## Drop, create and migrate database (⚠ DESTRUCTIVE)
	@echo "$(RED)⚠ This will destroy all data!$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		php bin/console doctrine:database:drop --force --if-exists; \
		$(MAKE) db-create; \
		$(MAKE) db-migrate; \
		echo "$(GREEN)✓ Database reset complete$(NC)"; \
	else \
		echo "$(YELLOW)Cancelled$(NC)"; \
	fi

db-fixtures: ## Load database fixtures
	@echo "$(BLUE)Loading fixtures...$(NC)"
	php bin/console doctrine:fixtures:load --no-interaction
	@echo "$(GREEN)✓ Fixtures loaded$(NC)"

migration: ## Create a new migration
	@echo "$(BLUE)Creating migration...$(NC)"
	php bin/console make:migration
	@echo "$(GREEN)✓ Migration created$(NC)"

test: ## Run tests
	@echo "$(BLUE)Running tests...$(NC)"
	php bin/phpunit
	@echo "$(GREEN)✓ Tests completed$(NC)"

test-unit: ## Run unit tests only
	@echo "$(BLUE)Running unit tests...$(NC)"
	php bin/phpunit tests/Unit
	@echo "$(GREEN)✓ Unit tests completed$(NC)"

test-integration: ## Run integration tests only
	@echo "$(BLUE)Running integration tests...$(NC)"
	php bin/phpunit tests/Integration
	@echo "$(GREEN)✓ Integration tests completed$(NC)"

test-functional: ## Run functional tests only
	@echo "$(BLUE)Running functional tests...$(NC)"
	php bin/phpunit tests/Functional
	@echo "$(GREEN)✓ Functional tests completed$(NC)"

cache-clear: ## Clear application cache
	@echo "$(BLUE)Clearing cache...$(NC)"
	php bin/console cache:clear
	@echo "$(GREEN)✓ Cache cleared$(NC)"

serve: ## Start development server
	@echo "$(BLUE)Starting development server...$(NC)"
	@echo "$(GREEN)Server running at http://localhost:8000$(NC)"
	@echo "$(YELLOW)Press Ctrl+C to stop$(NC)"
	php -S localhost:8000 -t public/

routes: ## Show all routes
	php bin/console debug:router

services: ## Show all services
	php bin/console debug:container

validate: ## Validate database schema
	php bin/console doctrine:schema:validate

lint: ## Lint PHP files
	@echo "$(BLUE)Linting PHP files...$(NC)"
	@find src -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
	@echo "$(GREEN)✓ Linting completed$(NC)"

clean: ## Clean var/ directory (cache, logs)
	@echo "$(BLUE)Cleaning var/ directory...$(NC)"
	rm -rf var/cache/* var/log/*
	@echo "$(GREEN)✓ Cleaned$(NC)"

.DEFAULT_GOAL := help
