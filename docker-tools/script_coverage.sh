#!/usr/bin/env sh

export COMPOSE_FILE=docker-compose-unit.yml

echo
echo
echo "Testing environment"
echo "==================="
echo "  COMPOSE_PROJECT_NAME:  ${COMPOSE_PROJECT_NAME}"
echo "  PHP_VERSION:           ${PHP_VERSION:-7.1}"
echo "  MYSQL_VERSION:         ${MYSQL_VERSION:-5.5}"
echo "  ELASTICSEARCH_VERSION: ${ELASTICSEARCH_VERSION}"
echo "  BEHAT_ARGS:            ${BEHAT_ARGS}"
echo "==================="
echo
echo

# Remove orphans from previous runs
docker-compose down -v --remove-orphans
docker-compose rm --force -v

# Update images
docker-compose pull

docker-compose up --remove-orphans  -d

echo "Wait for MySQL"
docker-compose run tools /tmp/wait.sh

echo "Run unit-shopware"
docker-compose run -eANT_OPTS=-D"file.encoding=UTF-8" tools ant -f /var/www/html/build/build.xml unit-shopware -Dapp.path="" -Dapp.host="web.example" -Ddb.host="mysql.example" -Ddb.port=3306 -Ddb.name="shopware" -Ddb.user="shopware" -Ddb.password="shopware"

echo "Run cleanup"
docker-compose run tools chown $(id -u):$(id -g) -R /var/www/html
docker-compose down -v --remove-orphans
docker-compose rm --force -v
