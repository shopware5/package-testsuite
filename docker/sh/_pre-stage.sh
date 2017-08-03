#!/bin/bash

export COMPOSE_PROJECT_NAME=$1
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

ENV_TESTS="../tests/.env"
ENV_TESTS_DIST="../tests/.env.dist"
BEHAT="../tests/behat.yml"
BEHAT_DIST="../tests/behat.yml.dist"
UPDATE_PACKAGE_NAME="*_update_*_latest.zip"
INSTALL_PACKAGE_NAME="*_install_*_latest.zip"

PACKAGE_VERSION="${PACKAGE_VERSION:-5.3}"
echo "PACKAGE_VERSION: $PACKAGE_VERSION"

echo "Checking for install package"
if [ ! -n "$(find ../files -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}")" ]
then
     echo "Error: No install package found!";
     exit 1;
fi

echo "Checking for update package"
if [ ! -n "$(find ../files -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}")" ]
then
     echo "Error: No update package found!";
     exit 1;
fi

echo "Create configuration"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
cp ${ENV_TESTS_DIST} ${ENV_TESTS}
cp ${BEHAT_DIST} ${BEHAT}

echo "Starting docker"
docker-compose down -v --remove-orphans
docker-compose pull
docker-compose up -d

echo "Wait for MySQL"
docker-compose run --rm tools /wait-mysql.sh

echo "Composer install in /tests"
docker-compose run --rm tools composer install