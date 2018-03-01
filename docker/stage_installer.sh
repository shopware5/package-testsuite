#!/bin/bash

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

. ./sh/_pre-stage.sh

echo "Unzipping installer"
docker-compose run --rm tools find /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run --rm tools chmod -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Setting extra config"
docker-compose run --rm tools bash -c 'cp /config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
docker-compose run --rm tools chown -R www-data:www-data /var/www/shopware

docker-compose run --rm tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@installer&&~@knownFailing'

. ./sh/_post-stage.sh