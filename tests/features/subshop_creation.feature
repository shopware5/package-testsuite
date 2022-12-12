@javascript @subshop @isolated
Feature: I can create and access a subshop

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Subshop-Kategorie > Subshop-Unterkategorie" exists

  Scenario: I can create a subshop
    Given I am logged into the backend
    Then I should see "Einstellungen" eventually
    When I hover backend menu item "Einstellungen"
    And I click the "Grundeinstellungen" menu element
    Then I should see "Shopeinstellungen" eventually

    When I click the "Shopeinstellungen" settings element
    Then I should see "Shops" eventually
    When I click the "Shops" settings element
    Then I should see "Hinzufügen" eventually
    When I click the "Hinzufügen" button

    And I fill in and submit the "Details" configuration form:
      | label                | value                    | type       |
      | Shop-Typ:            | Subshop                  | combobox   |
      | Name:                | SwagTestSubshop          | input      |
      | Titel:               | Mein Demosubshop         | input      |
      | Position:            | 1                        | input      |
      | Host:                | shopware-subshop-01.test | input      |
      | Hostalias:           | shopware-subshop-01.test | textarea   |
      | Währung:             | Euro                     | combobox   |
      | Lokalisierung:       | Deutsch (Deutschland)    | combobox   |
      | Kategorie:           | Subshop-Kategorie        | selecttree |
      | Template:            | Responsive               | combobox   |
      | Dokumenten-Template: | Responsive               | combobox   |
      | Kundengruppe:        | Shopkunden               | combobox   |
    And I am on the page "Index"

    Then I should be able to access the subshop via using "http://shopware-subshop-01.test"

    Then I should be able to access the shop via using "http://shopware.test/"
    And I should see "Newsletter" eventually
