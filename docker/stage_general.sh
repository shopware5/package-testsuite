#!/usr/bin/env sh
set -eu

. ./sh/_pre-stage.sh

echo "Unzipping installer"
compose run --rm --entrypoint="find" apache /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Install Shopware via CLI"
compose run --rm --entrypoint="php" apache /var/www/shopware/recovery/install/index.php \
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

echo "Run Mink"
compose run --rm behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '~@updater&&~@installer&&~@knownFailing'

. ./sh/_post-stage.sh
