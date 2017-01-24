@javascript @autoupdater
Feature: I can start and complete the update process

  Scenario: I can execute the update process in german language via auto-update
    Given I am on the page "BackendLogin"
    When I log in in with user "demo" and password "demo"
    Then I should see "Eine neue Version von Shopware ist verfügbar!"
    When I click on "Details"
    Then I should see "Softwareaktualisierung"
    And I should see "Shopware Version"
    And the "Update starten" button should be disabled

    When I click on the "Voraussetzungen" tab
    And the requirements are fullfilled
    And I click on the "Plugins" tab
    And the requirements are fullfilled
    And I confirm that I created a backup
    Then the "Update starten" button should be enabled so that the update can be started

    When I click the "Update starten" element
    Then I should see "Update erfolgreich gestartet"
    And I should see "Shopware Updater" eventually

    When I am on the page "UpdaterIndex"
    And I should see "Datenbank Update durchführen" eventually
    And I should see "Aufräumen" eventually

    When I have unused files in my installation
    And  I advance to the next updater page
    Then I should see "entfernte Dateien"

    When the cleanup will be finished and the loading indicator disappears
    Then I should see "Die Aktualisierung wurde erfolgreich abgeschlossen." eventually
    And I should see the link "shopFrontend" leading to "/"
    And I should see the link "shopBackend" leading to "/backend"

    When I am on the page "Index"
    Then I should see "Realisiert mit Shopware"
    When I am on the page "BackendLogin"
    Then I should see "Shopware Backend Login" eventually