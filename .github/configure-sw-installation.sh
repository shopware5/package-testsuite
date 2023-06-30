#!/usr/bin/env sh
set -eu

echo "Setting extra config"

cd /shopware && php bin/console sw:firstrunwizard:disable && php bin/console sw:cache:clear
# Preset API key
mysql -u root -proot shopware -e 'UPDATE s_core_auth SET `apiKey`="8mnq6vav02p3buc8h2q4q6n137" WHERE `roleID`=1;'
# Disable feedback form
mysql -u root -proot shopware -e 'UPDATE s_core_config_elements SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-send-feedback'"'"';'
# Disable benchmark teaser
mysql -u root -proot shopware -e 'UPDATE s_core_config_elements SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'benchmarkTeaser'"'"';'
# Disable update verification
mysql -u root -proot shopware -e 'UPDATE s_core_config_elements SET `value`='"'"'b:0;'"'"' WHERE `name`='"'"'update-verify-signature'"'"';'
# Use mocked update server
mysql -u root -proot shopware -e 'UPDATE s_core_config_elements SET `value`='"'"'s:23:"http://updates.example/";'"'"' WHERE `name`='"'"'update-api-endpoint'"'"';'

# Disable Cookie Banner
mysql -u root -proot shopware -e 'UPDATE s_core_config_elements SET value = "b:0;" WHERE name = "show_cookie_note"'

cd /shopware && php bin/console sw:cache:clear

echo "Chown directories to www-data"
chown -R www-data:www-data /shopware