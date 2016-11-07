#!/usr/bin/env bash

docker-compose down --remove-orphans

docker-compose run behat bash -c "ping shopware.localhost -c 2 && ./behat"