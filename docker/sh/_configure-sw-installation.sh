#!/usr/bin/env bash
set -euo pipefail

echo "Setting extra config"

# Disable first run wizard
docker-compose run --rm --entrypoint="bash" apache -c 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear'
# Preset API key
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_auth` SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
# Use custom testing config.php
docker-compose run --rm --entrypoint="bash" apache -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'
# Disable feedback form
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-send-feedback'"'"';'
# Disable benchmark teaser
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'benchmarkTeaser'"'"';'
# Disable update verification
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-verify-signature'"'"';'
# Use mocked update server
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'s:23:"http://updates.example/";'"'"' WHERE `name`='"'"'update-api-endpoint'"'"';'

# Disable Cookie Banner
docker-compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql shopware -e 'UPDATE s_core_config_elements SET value = "b:0;" WHERE name = "show_cookie_note"'

docker-compose run --rm --entrypoint="bash" apache -c 'cd /var/www/shopware && php bin/console sw:cache:clear'

echo "Chown directories to www-data"
docker-compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware

echo "Saving clean database state to sql dump"
docker-compose run --rm -w "/tests" --entrypoint="bash" apache -c 'mysqldump -u root -ptoor -h mysql shopware > clean_db.sql'
