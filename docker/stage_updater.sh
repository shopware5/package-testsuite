#!/bin/bash

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

. ./sh/_pre-stage.sh

echo "Starting sw install:release"

VERSION_TO_DOWNLOAD="latest"
if [ "$PACKAGE_VERSION" != "5.4" ]
    then
        VERSION_TO_DOWNLOAD="$PACKAGE_VERSION.0"
fi

docker-compose run --rm tools sw install:release \
--release=${VERSION_TO_DOWNLOAD} \
--install-dir=/var/www/shopware \
--db-host=mysql \
--db-user=shopware \
--db-password=shopware \
--db-name=shopware \
--shop-host=shopware.test \
-q \
-n

echo "Unzipping update"
docker-compose run --rm tools find /source -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}" -exec unzip -oq {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run --rm tools chmod -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Setting extra config"
docker-compose run --rm tools bash -c 'cp /config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
docker-compose run --rm tools chown -R www-data:www-data /var/www/shopware


echo "Run Mink"
docker-compose run --rm tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@updater&&~@knownFailing'

. ./sh/_post-stage.sh