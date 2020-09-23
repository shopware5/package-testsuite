#!/usr/bin/env bash

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

export COMPOSE_PROJECT_NAME=$1
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

echo "Cleanup"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
rm ../tests/clean_db.sql
docker-compose run --rm tools chown $(id -u):$(id -g) -R /tests
docker-compose down -v --remove-orphans
docker-compose rm --force -v