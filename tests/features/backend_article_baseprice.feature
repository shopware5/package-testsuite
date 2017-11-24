@javascript @backend @article @baseprices
Feature: I can generate and use article with base price information

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists
    And the following products exist in the store:
      | number  | name              | price | tax | supplier | categories                                       |
      | SW10001 | Einfacher Artikel | 10    | 19  | Finch    | Root > Deutsch > ErsteKategorie > Unterkategorie |

  Scenario: I can add graduated prices to the article
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    And I should see "Feedback" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Übersicht"
    Then I should see "SW10001" eventually
    When I click the edit icon of the entry "SW10001"
    Then I should see "Artikeldetails : Einfacher Artikel" eventually

    When I fill in the basic configuration:
      | label               | value | type     | fieldset             |
      | Maßeinheit:         | Liter | combobox | Grundpreisberechnung |
      | Inhalt:             | 0,25  | input    | Grundpreisberechnung |
      | Grundeinheit:       | 1     | input    | Grundpreisberechnung |
      | Verpackungseinheit: | Paket | input    | Grundpreisberechnung |
    Then I am able to save my article
    And I should see "Erfolgreich" eventually

    When I am on the homepage
    And I navigate to category tree "ErsteKategorie > Unterkategorie"
    Then I should see the base price information:
      | information | data    |
      | Grundpreis  | 40,00   |
      | Basis       | 1 Liter |
      | Inhalt      | 0.25    |
    When I am on the detail page for article with ordernumber "SW10001"
    Then I should see "Einfacher Artikel" eventually
    And I should see "Paket" eventually
    And I should see the base price information:
      | information | data    |
      | Grundpreis  | 40,00   |
      | Basis       | 1 Liter |
      | Einheit     | Paket   |
      | Inhalt      | 0.25    |
    When I put the current article "1" times into the basket
    Then I should see "Der Artikel wurde erfolgreich in den Warenkorb gelegt" eventually
    And I should see "10" eventually