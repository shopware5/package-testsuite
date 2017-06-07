#!/usr/bin/env sh

export COMPOSE_PROJECT_NAME=$1
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"
PACKAGE_NAME="*_install_*_latest.zip"
ENV_TESTS="../tests/.env"
ENV_TESTS_DIST="../tests/.env.dist"
BEHAT="../tests/behat.yml"
BEHAT_DIST="../tests/behat.yml.dist"

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

echo "Checking for install package"
if [ ! -n "$(find ../files -maxdepth 1 -name "${PACKAGE_NAME}")" ]
then
     echo "Error: No install package found!";
     exit 1;
fi

echo "Create configuration"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
cp ${ENV_TESTS_DIST} ${ENV_TESTS}
cp ${BEHAT_DIST} ${BEHAT}

echo "Starting docker"
docker-compose down -v --remove-orphans
docker-compose build
docker-compose up -d

echo "Wait for MySQL"
docker-compose run tools /wait-mysql.sh

echo "Composer install in /tests"
docker-compose run tools composer install

echo "Unzipping installer"
docker-compose run tools find /source -maxdepth 1 -name "${PACKAGE_NAME}" -exec unzip -q {} -d /var/www/shopware \;

echo "Chmod Cache directories"
docker-compose run tools chmod -R 777 /var/www/shopware/var /var/www/shopware/web/cache /var/www/shopware/files

echo "Chown directories to www-data"
docker-compose run tools chown -R www-data:www-data /var/www/shopware

echo "Install Shopware via CLI"
docker-compose run tools php /var/www/shopware/recovery/install/index.php \
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
docker-compose run tools bash -c 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear && cd -'
docker-compose run tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_auth` SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
docker-compose run tools bash -c 'cp /config_testing.php /var/www/shopware/config_testing.php'

echo "Chown directories to www-data"
docker-compose run tools chown -R www-data:www-data /var/www/shopware

echo "Run Mink"
docker-compose run tools ./behat --format=pretty --out=std --format=junit --out=/logs/mink --tags '~@autoupdater&&~@updater&&~@installer&&~@knownFailing'

echo "Cleanup"
[ -f "$ENV_TESTS" ] && rm "$ENV_TESTS"
[ -f "$BEHAT" ] && rm "$BEHAT"
docker-compose run tools chown $(id -u):$(id -g) -R /tests
docker-compose down -v --remove-orphans
docker-compose rm --force -v