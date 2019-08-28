#!/usr/bin/env sh

export COMPOSE_PROJECT_NAME=$1
export COMPOSE_FILE=docker-compose-package.yml
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

docker-compose run tools chown $(id -u):$(id -g) -R /source
docker-compose run tools chown $(id -u):$(id -g) -R /var/www/html
docker-compose down
docker-compose rm --force --all -v
