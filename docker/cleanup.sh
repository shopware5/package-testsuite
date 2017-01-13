#!/usr/bin/env bash

export COMPOSE_PROJECT_NAME=$1
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

echo "Cleanup"
docker-compose down -v --remove-orphans
docker-compose rm --force -v