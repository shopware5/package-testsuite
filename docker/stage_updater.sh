#!/usr/bin/env bash
set -euo pipefail

INSTALLER_URL='https://www.shopware.com/de/Download/redirect/version/sw5/file/install_5.6.0_3e81c54e1c57c6925e4d05336283ad18de9b10bb.zip'

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

trap "{ . ./sh/_post-stage.sh; exit 1; }" ERR

. ./sh/_pre-stage.sh

echo "Download & unpack Shopware v5.6.0"
docker-compose run --rm --entrypoint="bash" apache -c "wget -O /tmp/install.zip $INSTALLER_URL && unzip -d /var/www/shopware /tmp/install.zip"

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

echo "Unzipping update"
docker-compose run --rm --entrypoint="find" apache /source -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}" -exec unzip -oq {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run --rm --entrypoint="chmod" apache -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Setting extra config"
docker-compose run --rm --entrypoint="bash" apache -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
docker-compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware


echo "Run Mink"
docker-compose run --rm behat ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@updater&&~@knownFailing'

. ./sh/_post-stage.sh