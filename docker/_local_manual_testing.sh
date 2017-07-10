#!/usr/bin/env sh

ENV_TESTS="../tests/.env"
ENV_TESTS_DIST="../tests/.env.dist"
BEHAT="../tests/behat.yml"
BEHAT_DIST="../tests/behat.yml.dist"
UPDATE_PACKAGE_NAME="*_update_*_latest.zip"
INSTALL_PACKAGE_NAME="*_install_*_latest.zip"

echo "Checking for install package"
if [ ! -n "$(find ../files -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}")" ]
then
     echo "Error: No install package found!";
     exit 1;
fi

echo "Checking for update package"
if [ ! -n "$(find ../files -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}")" ]
then
     echo "Error: No update package found!";
     exit 1;
fi

echo "Create configuration"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
cp ${ENV_TESTS_DIST} ${ENV_TESTS}
cp ${BEHAT_DIST} ${BEHAT}
alias docker-compose="docker-compose -f docker-compose.yml -f docker-compose.local.yml"

echo "Starting docker"
docker-compose down -v --remove-orphans
docker-compose pull
docker-compose up -d

echo "Wait for MySQL"
docker-compose run --rm tools /wait-mysql.sh

echo "Composer install in /tests"
docker-compose run --rm tools composer install

echo "Unzipping installer"
docker-compose run --rm tools find /source -maxdepth 1 -name "${INSTALL_PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run --rm tools chmod -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Chown directories to www-data"
docker-compose run --rm tools chown -R www-data:www-data /var/www/shopware

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

echo "Setting extra config"
docker-compose run --rm tools bash -c 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear && cd -'
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_auth` SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
docker-compose run --rm tools bash -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-send-feedback'"'"';'
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-verify-signature'"'"';'
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'s:23:"http://updates.example/";'"'"' WHERE `name`='"'"'update-api-endpoint'"'"';'

echo "Chown directories to www-data"
docker-compose run --rm tools chown -R www-data:www-data /var/www/shopware

echo "Copying update package"
docker-compose run --rm tools find /source -maxdepth 1 -name "${UPDATE_PACKAGE_NAME}" -exec cp {} /var/www/cdn/update.zip \;

unalias docker-compose