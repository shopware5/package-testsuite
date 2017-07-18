#!/bin/bash

echo "Setting extra config"

# Disable first run wizard
docker-compose run --rm tools bash -c 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear'
# Preset API key
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_auth` SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
# Use custom testing config.php
docker-compose run --rm tools bash -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'
# Disable feedback form
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-send-feedback'"'"';'
# Disable update verification
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-verify-signature'"'"';'
# Use mocked update server
docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'s:23:"http://updates.example/";'"'"' WHERE `name`='"'"'update-api-endpoint'"'"';'

# Shopware 5.3 specific configuration
if [ "$PACKAGE_VERSION" = "5.3" ]
    then
        # Disable installation survey
        docker-compose run --rm tools mysql -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'installationSurvey'"'"';'
        # Disable RC message
        docker-compose run --rm tools bash -c "cd /var/www/shopware && sed -ie \"s/const VERSION_TEXT = '.*'/const VERSION_TEXT = ''/g\" engine/Shopware/Application.php"
fi

echo "Chown directories to www-data"
docker-compose run --rm tools chown -R www-data:www-data /var/www/shopware

