@javascript @backend @article @properties
Feature: I can add properties to an article

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists
    And the following products exist in the store:
      | number  | name                | price | tax | supplier | categories                                       |
      | SW66666 | Einfacher Artikel 6 | 10    | 19  | Finch    | Root > Deutsch > ErsteKategorie > Unterkategorie |
    And the following properties exist in the store:
      | key | groupKey | set       | group | option |
      | 1   | 1        | ErstesSet | Farbe | Blau   |

  Scenario: I can add properties to an article
    Given I am logged into the backend
    Then I should see "Artikel" eventually

    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Übersicht"
    Then I should see "SW66666" eventually
    When I click the edit icon of the entry "SW66666"
    Then I should see "Artikeldetails : Einfacher Artikel 6" eventually

    When I click on the "Eigenschaften" tab
    Then I should see "Set auswählen:" eventually
    When I fill in the property configuration:
      | label                 | value     | type         | fieldset      |
      | Set auswählen:        | ErstesSet | combobox     | Eigenschaften |
      | Eigenschaft zuweisen: | Farbe     | combobox     | Eigenschaften |
      | Bitte wählen...       | Blau      | withoutlabel | Eigenschaften |
    Then I should see "Blau" as corresponding value to "Farbe"
    And I am able to save my article
    And I should see "Erfolgreich" eventually

    When I am on the detail page for article with ordernumber "SW66666"
    Then I should see "Blau" eventually
    When I should see "Einfacher Artikel 6" eventually
    And I put the current article "1" times into the basket
    Then I should see "Der Artikel wurde erfolgreich in den Warenkorb gelegt" eventually
    And I should see "10" eventually
    When I click on "Warenkorb bearbeiten"
    Then I should see "Einfacher Artikel 6" eventually
    And I should see "10,00" eventually
