# Local development environment for the login service + legacy site E2E stack.
#
#   make setup   - one-shot bootstrap: env files, dependencies, containers, DB
#
# Everything runs in containers; no PHP/composer needed on the host.

COMPOSE      = docker compose
EXEC         = $(COMPOSE) exec -T login
WEBSITE_ROOT = ../website_root
COMPOSER_IMG = docker run --rm --entrypoint sh -v $(CURDIR)/..:/repos composer:2 -c

.PHONY: help setup env deps up db users test dusk e2e lint fix down destroy logs

help:
	@echo "Targets:"
	@echo "  setup    one-shot local environment bootstrap (env + deps + up + db)"
	@echo "  env      create .env files from the committed dev templates"
	@echo "  deps     install composer dependencies (login + website_root/classes)"
	@echo "  up       build and start all containers"
	@echo "  db       migrate:fresh, seed, and create the local user hierarchy"
	@echo "  users    (re)run the local user hierarchy command only"
	@echo "  test     run the phpunit suite (MySQL test database)"
	@echo "  dusk     run Laravel Dusk browser tests (needs 'make db' state + selenium up)"
	@echo "  lint     check code style (pint --test)"
	@echo "  fix      fix code style (pint)"
	@echo "  e2e      run the login -> MyCurriculum session handoff test"
	@echo "  down     stop containers"
	@echo "  destroy  stop containers and delete the database volume"
	@echo "  logs     tail container logs"

setup: env deps up db
	@echo
	@echo "Ready:"
	@echo "  login app   http://localhost:8090   (dev@example.com / password)"
	@echo "  legacy site http://localhost:8091/MyCurriculum.php"
	@echo "  mailhog     http://localhost:18025"

env:
	@test -f .env || (cp .env.dev .env && echo "created .env from .env.dev")
	@test -f $(WEBSITE_ROOT)/.env || (grep -v '^#' docker/website.env.dev > $(WEBSITE_ROOT)/.env && echo "created $(WEBSITE_ROOT)/.env")
	@test -f storage/saml/sp.key || (mkdir -p storage/saml && \
		openssl req -x509 -newkey rsa:2048 -keyout storage/saml/sp.key -out storage/saml/sp.crt -days 1825 -nodes -subj "/CN=local-login-sp" 2>/dev/null && \
		echo "generated local SP keypair in storage/saml/")

deps:
	@test -d vendor || (echo "installing login composer deps..." && \
		$(COMPOSER_IMG) "git config --global --add safe.directory /repos/login && composer install --working-dir=/repos/login --ignore-platform-reqs --no-interaction")
	@test -d $(WEBSITE_ROOT)/classes/vendor || (echo "installing website_root classes deps..." && \
		mkdir -p $(WEBSITE_ROOT)/Trax/class && \
		$(COMPOSER_IMG) "git config --global --add safe.directory /repos/website_root && composer install --working-dir=/repos/website_root/classes --ignore-platform-reqs --no-dev --no-interaction")

up:
	$(COMPOSE) up -d --build

db:
	# Wipe explicitly instead of migrate:fresh: fresh only wipes when the
	# migration repository table exists, and ours is the app-specific
	# migrations_login (shared prod DB) — on a DB built before the rename,
	# fresh would skip the wipe and collide with the schema dump.
	$(EXEC) php artisan db:wipe --force
	$(EXEC) php artisan migrate --seed
	$(EXEC) php artisan local:users
	$(EXEC) sh -c "curl -sf -H 'Host: localhost:$${MOCK_IDP_PORT:-8092}' http://mock-idp:8080/simplesaml/saml2/idp/metadata.php -o /tmp/mock-idp-metadata.xml \
		&& php artisan saml:client update local-idp --metadata=/tmp/mock-idp-metadata.xml \
		&& php artisan saml:client enable local-idp" \
		|| echo "mock-idp not reachable; SAML client left disabled (run 'docker compose up -d mock-idp' then 'make db')"
	$(EXEC) sh -c "curl -sf -H 'Host: localhost:$${MOCK_IDP_ADMIN_PORT:-8093}' http://mock-idp-admin:8080/simplesaml/saml2/idp/metadata.php -o /tmp/mock-idp-admin-metadata.xml \
		&& php artisan saml:client update local-admin-idp --metadata=/tmp/mock-idp-admin-metadata.xml \
		&& php artisan saml:client enable local-admin-idp" \
		|| echo "mock-idp-admin not reachable; admin SSO client left disabled (run 'docker compose up -d mock-idp-admin' then 'make db')"

users:
	$(EXEC) php artisan local:users

test:
	$(EXEC) php artisan test

dusk:
	$(EXEC) php artisan dusk

lint:
	$(EXEC) vendor/bin/pint --test

fix:
	$(EXEC) vendor/bin/pint

e2e:
	./tests/e2e/session-handoff.sh
	./tests/e2e/saml-login.sh

down:
	$(COMPOSE) down

destroy:
	$(COMPOSE) down -v

logs:
	$(COMPOSE) logs -f --tail=50
