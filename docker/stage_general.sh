#!/bin/bash

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

. ./sh/_pre-stage.sh

echo "Unzipping installer"
docker-compose run --rm tools find /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

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

. ./sh/_configure-sw-installation.sh

if [ "$PACKAGE_VERSION" = "5.2" ]
    then
        echo "Run Mink (5.2 Compatibility mode)"
        docker-compose run --rm tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '~@updater&&~@installer&&~@knownFailing&&~@shopware53'
    else
        echo "Run Mink"
        docker-compose run --rm tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '~@updater&&~@installer&&~@knownFailing&&~@shopware52'
fi

. ./sh/_post-stage.sh