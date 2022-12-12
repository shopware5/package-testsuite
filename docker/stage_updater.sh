#!/usr/bin/env sh
set -eu

INSTALLER_URL='https://releases.shopware.com/install_5.6.10_b9471cf7c3f30dfc05d7c959f555c2a8d1c24420.zip'

. ./sh/_pre-stage.sh

echo "Download & unpack Shopware v5.6.0"
compose run --rm --entrypoint="bash" apache -c "wget -O /tmp/install.zip $INSTALLER_URL && unzip -d /var/www/shopware /tmp/install.zip"

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

echo "Unzipping update"
compose run --rm --entrypoint="find" apache /source -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}" -exec unzip -oq {} -d /var/www/shopware \;

echo "Chmod Cache directories"
compose run --rm --entrypoint="chmod" apache -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Setting extra config"
compose run --rm --entrypoint="bash" apache -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware

echo "Run Mink"
compose run --rm behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '@updater&&~@knownFailing'

. ./sh/_post-stage.sh
