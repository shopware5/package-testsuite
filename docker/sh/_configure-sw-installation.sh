#!/usr/bin/env sh
set -eu

echo "Setting extra config"

# Disable first run wizard
compose run --rm --entrypoint="bash" apache -c 'cd /var/www/shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear'
# Preset API key
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_auth` SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
# Use custom testing config.php
compose run --rm --entrypoint="bash" apache -c 'cp /php-config/config_testing.php /var/www/shopware/config_testing.php'
# Disable feedback form
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-send-feedback'"'"';'
# Disable benchmark teaser
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'benchmarkTeaser'"'"';'
# Disable update verification
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-verify-signature'"'"';'
# Use mocked update server
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql -e 'UPDATE `shopware`.`s_core_config_elements` SET `value`='"'"'s:23:"http://updates.example/";'"'"' WHERE `name`='"'"'update-api-endpoint'"'"';'

# Disable Cookie Banner
compose run --rm --entrypoint="mysql" apache -u root -ptoor -h mysql shopware -e 'UPDATE s_core_config_elements SET value = "b:0;" WHERE name = "show_cookie_note"'

compose run --rm --entrypoint="bash" apache -c 'cd /var/www/shopware && php bin/console sw:cache:clear'

echo "Chown directories to www-data"
compose run --rm --entrypoint="chown" apache -R www-data:www-data /var/www/shopware

echo "Saving clean database state to sql dump"
compose run --rm -w "/tests" --entrypoint="bash" apache -c 'mysqldump -u root -ptoor -h mysql shopware > clean_db.sql'
