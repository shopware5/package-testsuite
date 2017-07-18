#!/bin/bash

echo "Cleanup"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
docker-compose run --rm tools chown $(id -u):$(id -g) -R /tests
docker-compose down -v --remove-orphans
docker-compose rm --force -v