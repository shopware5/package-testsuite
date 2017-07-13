@javascript @backend
Feature: I can manage my customers with the backend customer module

  Background:
    Given there is no customer registered with e-mail address "test@tester.com"

  Scenario: I can create, edit and delete customers from the customer module

    ## Opening the module
    Given I am on the page "BackendLogin"
    When I log in with user "demo" and password "demo"
    And I hover backend menu item "Kunden"
    And I click on backend menu item that contains "Kunden"
    Then I might need to close the welcome wizard
    And I should see "Hinzufügen" eventually

    ## Creating a new customer
    When I click the "Hinzufügen" Button
    Then I should see "Kunden-Administration" eventually

    When I fill out the new customer form:
      | label                 | value           | type       | fieldset          |
      | E-Mail:               | test@tester.com | input      | Stammdaten        |
      | Kundengruppe:         | Shopkunden      | combobox   | Stammdaten        |
      | Shop:                 | Demo shop       | combobox   | Stammdaten        |
      | Passwort:             | 12345678        | input      | Stammdaten        |
      | Passwort bestätigen:  | 12345678        | input      | Stammdaten        |
      | Anrede:               | Herr            | combobox   | Persönliche Daten |
      | Vorname:              | Pep             | input      | Persönliche Daten |
      | Nachname:             | Eroni           | input      | Persönliche Daten |
      | Anrede:               | Herr            | combobox   | Adressdaten       |
      | Vorname:              | Pep             | input      | Adressdaten       |
      | Nachname:             | Eroni           | input      | Adressdaten       |
      | Straße:               | Drake Circus 1  | input      | Adressdaten       |
      | Postleitzahl:         | PL4 1BB         | input      | Adressdaten       |
      | Stadt:                | Plymouth        | input      | Adressdaten       |
      | Land:                 | Großbritannien  | combobox   | Adressdaten       |
      | Aktuelle Zahlungsart: | Vorkasse        | paymentbox | Zahlungsdaten     |

    And I click the "Speichern" Button
    Then I should see "Pep" eventually

    ## Editing the newly created user
    When I click the edit icon on customer "Pep"
    Then I should see "Stammdaten" eventually

    When I change the following information:
      | label     | value | type  | fieldset          |
      | Vorname:  | David | input | Persönliche Daten |
      | Nachname: | Bowie | input | Persönliche Daten |

    And I click the "Speichern" Button
    Then I should see "David" eventually
    And I should see "Bowie" eventually

    ## Deleting the newly created user
    When I click the delete icon on customer "David"
    Then I should see "Sind Sie sicher, dass" eventually

    When I click the "Ja" Button
    Then I should eventually not see "David"
