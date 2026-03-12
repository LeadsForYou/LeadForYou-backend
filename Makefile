.PHONY: up build install_dependencies generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys install_phpunit tests_coverage

up:
	docker compose up -d

build:
	docker compose down && docker compose build && docker compose up -d

down:
	docker compose down

container_php:
	docker compose exec -it php bash

container_database:
	docker compose exec -it database bash

composer_install:
	docker compose exec -T php bash -c "composer install"

db_diff:
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:diff -n"

db_migrate:
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:migrate -n"

db_fixtures:
	docker compose exec -T php bash -c "php bin/console doctrine:fixtures:load -n --append"

db_setup_test:
	docker compose exec -T php bash -c "php bin/console doctrine:database:create --env=test --if-not-exists -n"
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:migrate --env=test -n"

tests: db_setup_test
	docker compose exec -T php bash -c "php bin/phpunit"

tests_integration: db_setup_test
	docker compose exec -T php bash -c "php bin/phpunit --testsuite Integration"

tests_coverage: db_setup_test
	docker compose exec -T php bash -c "XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage/html --coverage-text"

cache_clear:
	docker compose exec -T php bash -c "php bin/console cache:clear"

reset:
	rm -rf var/
	docker compose exec -T php bash -c "php bin/console cache:clear"
	docker compose exec -T php bash -c "php bin/console d:d:d -f"
	docker compose exec -T php bash -c "php bin/console d:d:c"
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:migrate -n"

code_style:
	docker compose exec -T php bash -c "php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff -vvv"

style:
	docker compose exec -T php bash -c "php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff -vvv"

generate_keys:
	docker compose exec -T php bash -c "php bin/console lexik:jwt:generate-keypair --overwrite"

# Comando iniciar o projeto do zero
setup: up install_dependencies generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys