@javascript @installer
Feature: I can start and complete the installation process

  @shopware52
  Scenario: I can execute the installation process
    Given I am on the page "InstallerIndex"
    Then I should see "Sprache wählen"
    When I advance to the next installer page
    Then I should see the list of required files and folders:
      | text                               |
      | config.php                         |
      | var/cache/                         |
      | web/cache/                         |
      | files/documents/                   |
      | recovery/                          |
      | engine/Shopware/Plugins/Community/ |
      | engine/Shopware/Plugins/Default/   |
      | themes/Frontend                    |
      | media/archive/                     |
      | media/image/                       |
      | media/image/thumbnail/             |
      | recovery/install/data              |
    And I should see the checks for my system:
      | text                |
      | php                 |
      | ionCube Loader      |
      | mod_rewrite         |
      | pdo                 |
      | disk_free_space     |
      | disk_free_space     |
      | curl                |
      | json                |
      | xml                 |
      | include_path        |
      | memory_limit        |
      | max_execution_time  |
      | upload_max_filesize |
      | allow_url_fopen     |
      | zip                 |
      | ftp                 |
    When I advance to the next installer page

    Then I should see "Shopware 5 ist dual lizenziert. Die Endnutzer-Lizenzbestimmungen („EULA“) für die Professional-"
    When I advance to the next installer page

    Then I should see "Sie müssen unseren Lizenzbestimmungen zustimmen"
    When I check the license checkbox to agree to the terms
    And I advance to the next installer page

    Then the following form fields must be required:
      | fieldname         |
      | c_database_user   |
      | c_database_schema |
    When I fill the "databaseForm" form:
      | field               | value    |
      | c_database_host     | mysql    |
      | c_database_port     | 3306     |
      | c_database_user     | shopware |
      | c_database_password | shopware |
      | c_database_schema   | shopware |
    And I advance to the next installer page

    And I click on "start" on the installer page to start the database update
    Then I should see "Datenbank Update wurde erfolgreich durchgeführt" after the database import has finished
    When I go back to the previous installer page
    Then I should see "Datenbank konfigurieren"
    When I advance to the next installer page
    And I click "Überspringen" to skip the next installer page
    Then I should see "Wählen Sie die Lizenz"
    When I choose the radio field with value "cm"
    Then the "licenseAgreement" field should get activated so that I am able to enter the license
    When I choose the radio field with value "ce"
    And I advance to the next installer page

    Then the following form fields must be required:
      | fieldname               |
      | c_config_shopName       |
      | c_config_mail           |
      | c_config_admin_name     |
      | c_config_admin_username |
      | c_config_admin_email    |
      | c_config_admin_password |
    When I fill the "shopBasicConfiguration" form:
      | field                   | value               |
      | c_config_shopName       | Demoshop            |
      | c_config_mail           | your.email@shop.com |
      | c_config_admin_name     | Demo-Admin          |
      | c_config_admin_username | demo                |
      | c_config_admin_email    | demo@demo.de        |
      | c_config_admin_password | demo                |
    And I advance to the next installer page
    Then I should see "Die Installation wurde erfolgreich abgeschlossen."
    And I should see the link "shopFrontend" leading to "/"
    And I should see the link "shopBackend" leading to "/backend"
    When I am on the page "Index"
    Then I should see "Realisiert mit Shopware"
    When I am on the page "BackendLogin"
    Then I should see "Shopware Backend Login" eventually

  @shopware53
  Scenario: I can execute the installation process
    Given I am on the page "InstallerIndex"
    Then I should see "Deine Shopware-Installation"
    When I advance to the next installer page
    Then I should see "Alle Voraussetzungen für eine erfolgreiche Installation sind erfüllt"

    When I advance to the next installer page

    Then I should see "Endnutzer-Lizenzbestimmungen"
    When I check the license checkbox to agree to the terms
    And I advance to the next installer page

    Then the following form fields must be required:
      | fieldname         |
      | c_database_user   |
      | c_database_schema |
    When I fill the "databaseForm" form:
      | field               | value    |
      | c_database_host     | mysql    |
      | c_database_port     | 3306     |
      | c_database_user     | shopware |
      | c_database_password | shopware |
      | c_database_schema   | shopware |
    And I advance to the next installer page

    And I click on "start" on the installer page to start the database update
    Then I should see "Datenbank erfolgreich importiert!" after import is finished
    When I go back to the previous installer page
    Then I should see "Datenbank konfigurieren"
    When I advance to the next installer page
    And I click "Überspringen" to skip the next installer page
    Then I should see "Hast du eine Shopware-Lizenz erworben?"
    When I choose the radio field with value "cm"
    Then the "licenseAgreement" field should get activated so that I am able to enter the license
    When I choose the radio field with value "ce"
    And I advance to the next installer page

    Then the following form fields must be required:
      | fieldname               |
      | c_config_shopName       |
      | c_config_mail           |
      | c_config_admin_name     |
      | c_config_admin_username |
      | c_config_admin_email    |
      | c_config_admin_password |
    When I fill the "shopBasicConfiguration" form:
      | field                   | value               |
      | c_config_shopName       | Demoshop            |
      | c_config_mail           | your.email@shop.com |
      | c_config_admin_name     | Demo-Admin          |
      | c_config_admin_username | demo                |
      | c_config_admin_email    | demo@demo.de        |
      | c_config_admin_password | demo                |
    And I advance to the next installer page
    Then I should see "Installation abgeschlossen"
    And I should see the link "shopFrontend" leading to "/"
    And I should see the link "shopBackend" leading to "/backend"
    When I am on the page "Index"
    Then I should see "Realisiert mit Shopware"
    When I am on the page "BackendLogin"
    Then I should see "Shopware Backend Login" eventually
