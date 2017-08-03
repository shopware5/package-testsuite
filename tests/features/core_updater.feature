@javascript @updater
Feature: I can start and complete the update process

  Background:
    Given the update requirements are met

  Scenario: My shop is in maintenance mode while updating
    Given I am on the page "Index"
    Then I should see "Unsere Website befindet sich gerade in der Wartung."

  Scenario: I can execute the update process manually
    Given I am on the page "UpdaterIndex"
    Then I should see "Sprache wählen"
    And I should see "Deutsch"

    When I advance to the next updater page
    Then I should see "Datenbank Update durchführen"

    When I start the database migration
    Then I should see "Abhänging von der Menge der aufzuräumenden Dateien kann dieser Prozess einige Zeit in Anspruch nehmen." eventually
    And I should see "Aufräumen" eventually
    When I have unused files in my installation
    And  I advance to the next updater page
    Then I should see "entfernte Dateien"

    When I should see "Die Aktualisierung wurde erfolgreich abgeschlossen." eventually
    Then I should see the reminder "Ihr Shop befindet sich zurzeit im Wartungsmodus." to remove the update-assets folder

  Scenario: The system requirements for the update are not fullfilled
    Given the update requirements are not met
    And I am on the page "UpdaterIndex"
    Then I should see "Sprache wählen"
    And I should see "Deutsch"

    When I advance to the next updater page
    Then I should see "Einige Voraussetzungen werden nicht erfüllt"
    When I advance to the next step via "requirementForwardButton"
    Then I should see "Einige Voraussetzungen werden nicht erfüllt"
    When I correct the requirements
    And I advance to the next step via "requirementForwardButton"
    Then I should see "Datenbank Update durchführen"

    When I start the database migration
    Then I should see "Abhänging von der Menge der aufzuräumenden Dateien kann dieser Prozess einige Zeit in Anspruch nehmen." eventually
    When I have unused files in my installation
    And  I advance to the next updater page
    Then I should see "entfernte Dateien"

    When I should see "Die Aktualisierung wurde erfolgreich abgeschlossen." eventually
    Then I should see the reminder "Ihr Shop befindet sich zurzeit im Wartungsmodus." to remove the update-assets folder