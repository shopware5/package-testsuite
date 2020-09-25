#!/usr/bin/env bash
set -euo pipefail

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

trap "{ . ./sh/_post-stage.sh; exit 1; }" ERR

. ./sh/_pre-stage.sh

echo "Unzipping installer"
docker-compose run --rm --entrypoint="find" apache /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run --rm --entrypoint="chmod" apache -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Setting extra config"
docker-compose run --rm --entrypoint="bash" apache -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
docker-compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware

docker-compose run --rm behat ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@installer&&~@knownFailing'

. ./sh/_post-stage.sh