#!/usr/bin/env bash

# Include testing config for docker-compose
shopt -s expand_aliases
alias docker-compose="docker-compose -f docker-compose.yml -f docker-compose.local.yml"

source sh/_pre-stage.sh

echo "Unzipping installer"
docker-compose run --rm tools find /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Copying update package"
docker-compose run --rm tools find /source -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}" -exec cp {} /var/www/cdn/update.zip \;

echo "Install Shopware via CLI"
docker-compose run --rm tools php /var/www/shopware/recovery/install/index.php \
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

source sh/_configure-sw-installation.sh

unalias docker-compose