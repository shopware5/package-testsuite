#!/usr/bin/env sh
set -eu

if [ -z "${1:-}" ]; then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

export COMPOSE_PROJECT_NAME=${1}
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

# Include testing config for docker compose when running locally
if [ -z "${CI:-}" ]; then
  alias compose="docker compose -f docker-compose.yml -f docker-compose.local.yml"
else
  alias compose="docker compose -f docker-compose.yml"
fi

ENV_TESTS="../tests/.env"
ENV_TESTS_DIST="../tests/.env.dist"
BEHAT="../tests/behat.yml"
BEHAT_DIST="../tests/behat.yml.dist"
UPDATE_PACKAGE_NAME="*_update_*_latest.zip"
INSTALL_PACKAGE_NAME="*_install_*_latest.zip"

PACKAGE_VERSION="${PACKAGE_VERSION:-5.7}"
echo "PACKAGE_VERSION: $PACKAGE_VERSION"

echo "Checking for install package"
if [ -z "$(find ../files -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}")" ]
then
     echo "Error: No install package found!";
     exit 1;
fi

echo "Checking for update package"
if [ -z "$(find ../files -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}")" ]
then
     echo "Error: No update package found!";
     exit 1;
fi

echo "Create configuration"
[ -f "$ENV_TESTS" ] && rm -f "$ENV_TESTS"
[ -f "$BEHAT" ] && rm -f "$BEHAT"
cp ${ENV_TESTS_DIST} ${ENV_TESTS}
cp ${BEHAT_DIST} ${BEHAT}

echo "Pulling images"
compose pull -q

echo "Starting services"
compose up -d mysql apache selenium

compose run --rm wait-for-mysql
compose run --rm wait-for-selenium

echo "Composer install in /tests"
compose run --rm -w "/tests" --entrypoint="composer" apache install --ignore-platform-req=composer-plugin-api
