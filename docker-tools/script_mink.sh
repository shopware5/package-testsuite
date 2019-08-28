#!/usr/bin/env sh

export COMPOSE_FILE=docker-compose-mink.yml

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

echo "Create cache directories"
docker-compose run tools mkdir -p /var/www/html/var/cache
docker-compose run tools mkdir -p /var/www/html/web/cache

echo "Run Build"
docker-compose run -eANT_OPTS=-D"file.encoding=UTF-8" tools ant -f /var/www/html/build/build.xml build-unit -Dapp.path="" -Dapp.host="web.example" -Ddb.host="mysql.example" -Ddb.port=3306 -Ddb.name="shopware" -Ddb.user="shopware" -Ddb.password="shopware"

# Do some config foo
echo "Disable CSRF, enable debug mode"
docker-compose run tools php -r '$config = include "config.php"; $config["front"] = ["showException" => true]; $config["phpsettings"]= ["display_errors" => 1, "error_reporting" => -1]; $config["csrfProtection"] = ["frontend" => false, "backend" => false]; file_put_contents("config.php", "<?php return " . var_export($config, true) . ";");'

echo "Chmod Cache directories"
docker-compose run tools chmod -R 777 /var/www/html/var /var/www/html/web/cache

echo "Run Mink"
echo "> vendor/bin/behat --config tests/Mink/behat.yml.dist --strict --format=pretty --out=std --format=junit --out=build/logs/mink ${BEHAT_ARGS}"
docker-compose run -eBEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"base_url" : "http://web.example"}}}' tools vendor/bin/behat --strict --config tests/Mink/behat.yml.dist --format=pretty --out=std --format=junit --out=build/logs/mink ${BEHAT_ARGS}

echo "Run cleanup"
docker-compose run tools chown $(id -u):$(id -g) -R /var/www/html
docker-compose down -v --remove-orphans
docker-compose rm --force -v
