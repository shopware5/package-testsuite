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

echo "Install Shopware via CLI"
docker-compose run --rm --entrypoint="php" apache /var/www/shopware/recovery/install/index.php \
    --no-interaction \
    --db-host="mysql" \
    --db-name="shopware" \
    --db-user="shopware" \
    --db-password="shopware" \
    --shop-locale="de_DE" \
    --shop-host="shopware.test" \
    --shop-currency="EUR" \
    --admin-username="demo" \
    --admin-password="demo" \
    --admin-email="demo@demo.demo" \
    --admin-locale="de_DE" \
    --admin-name="Demouser"

. ./sh/_configure-sw-installation.sh

echo "Fetching 5.4.0 package"
docker-compose run --rm --entrypoint="rm" apache -rf /var/www/shopware/* \
 && wget "${bamboo_PACKAGE_URL}" --output-file=shopware.zip \
 && unzip shopware.zip -d /var/www/shopware

echo "Chown directories to www-data"
docker-compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware

echo "Prevent recovery"
docker-compose run --rm --entrypoint="touch" apache recovery/install/data/install.lock

echo "Run Mink"
docker-compose run --rm -w "/tests" --entrypoint="./behat" apache --format=pretty --out=std --format=junit --out=/logs/mink --tags '~@updater&&~@installer&&~@knownFailing&&~@shopware52'

. ./sh/_post-stage.sh