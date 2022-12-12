@javascript @installer
Feature: I can start and complete the installation process

  Scenario: I can execute the installation process
    Given I am on the page "InstallerIndex"
    Then I should see "Deine Shopware-Installation"
    When I advance to the next installer page
    Then I should see "Alle Voraussetzungen für eine erfolgreiche Installation sind erfüllt"

    When I advance to the next installer page

    Then I should see "Allgemeine Geschäftsbedingungen" eventually
    When I check the license checkbox to agree to the terms
    And I advance to the next installer page

    Then the following form fields must be required:
      | fieldname         |
      | c_database_user   |
      | c_database_schema |
    When I fill the form:
      | field               | value    |
      | c_database_host     | localhost|
      | c_database_port     | 3306     |
      | c_database_user     | root     |
      | c_database_password | root     |
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
    When I fill the form:
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
    When I am on the page "Backend"
    Then I should see "Shopware Backend Login" eventually
