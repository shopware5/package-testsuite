#!/usr/bin/env bash

export COMPOSE_PROJECT_NAME=$1
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

docker-compose down -v --remove-orphans
docker-compose build
docker-compose up -d

echo "Wait for MySQL"
docker-compose run tools /wait-mysql.sh

echo "Composer install in /tests"
docker-compose run tools composer install

echo "Unzipping installer"
docker-compose run tools find /source -maxdepth 1 -name "*_install_*_latest.zip" -exec unzip -q {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run tools chmod -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Chown directories to www-data"
docker-compose run tools chown -R www-data:www-data /var/www/shopware

echo "Run Mink"
docker-compose run tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@installer'

echo "Cleanup"
docker-compose run tools chown $(id -u):$(id -g) -R /tests
docker-compose down -v --remove-orphans
docker-compose rm --force -v