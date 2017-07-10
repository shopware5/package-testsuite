@javascript @autoupdater
Feature: I can start and complete the update process

  Background:
    Given the auto update requirements are not met

  Scenario: I can start and complete the auto-update process with corrected system requirements
    Given I am on the page "BackendLogin"
    When I log in with user "demo" and password "demo"
    Then I should see "Eine neue Version von Shopware ist verfügbar!" eventually
    When I click on "Details" to look at the update details

    Then I should see "Softwareaktualisierung" eventually
    And I should see "Versionshinweise"
    And the "Update starten" button should be disabled

    When I click on the "Voraussetzungen" tab
    And the listed requirements are not fullfilled
    And I confirm that I created a backup
    Then I should see "Bitte überprüfen Sie den Voraussetzungen Tab" eventually
    And the "Update starten" button should be disabled
    When I correct the auto update requirements
    And I refresh the window
    And I confirm that I created a backup
    Then the listed requirements are fullfilled:
      | message                          |
      | Erforderliche PHP Version        |
      | Erforderliche MySQL Version      |
      | Sie benutzen die Shopware CE     |
      | Kein Emotion Template verwendet. |
      | testPathApache/files             |
      | testPathApache                   |
    And the "Update starten" button should be enabled so that the update can be started

    When I click the "Update starten" element
    Then I should see "Update vorbereiten" eventually
    And I should see "Datenbank Update durchführen" eventually
    And I should see "Die folgenden Dateien gehören zu einer früheren Shopware Version und werden nach diesem Update nicht länger benötigt." eventually

    When I have unused files in my installation
    And  I advance to the next updater page
    Then I should see "entfernte Dateien"

    When the cleanup will be finished and the loading indicator disappears
    Then I should see "Die Aktualisierung wurde erfolgreich abgeschlossen." eventually
    And I should see the link "shopFrontend" leading to "/recovery/update/index.php/redirect/frontend" after the update
    And I should see the link "shopBackend" leading to "/recovery/update/index.php/redirect/backend" after the update

    When I click the "shopFrontend" updater element
    And I am on the page "Index"
    Then I should see "Realisiert mit Shopware"
    When I am on the page "BackendLogin"
    Then I should see "Widgets" eventually