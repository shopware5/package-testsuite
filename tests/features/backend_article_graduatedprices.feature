@javascript @backend @article @graduatedprices
Feature: I can generate and use article with graduated prices

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists
    And the following products exist in the store:
      | number  | name              | price | tax | supplier | categories                                       |
      | SW88888 | Einfacher Artikel | 10    | 19  | Finch    | Root > Deutsch > ErsteKategorie > Unterkategorie |

  Scenario: I can add graduated prices to the article
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Übersicht"
    Then I should see "SW88888" eventually
    When I click the edit icon of the entry "SW88888"
    Then I should see "Artikeldetails : Einfacher Artikel" eventually

    When I limit the price "10" for an amount up to "2"
    Then I should see "2" as to-price
    And I should see "3" as from-price to any number
    When I limit the price "7" for an amount up to "5"
    Then I should see "6" as from-price to any number
    When I set the price "5" for any number from here
    Then I am able to save my article
    And I should see "Erfolgreich" eventually

    When I am on the homepage
    And I navigate to category tree "ErsteKategorie > Unterkategorie"
    And I am on the detail page for article with ordernumber "SW88888"
    And I should see "Einfacher Artikel" eventually
    Then I should see the following graduated prices:
      | amount | price |
      | 2      | 10,00 |
      | 3      | 7,00  |
      | 6      | 5,00  |
    And I put the current article "1" times into the basket
    Then I should see "Der Artikel wurde erfolgreich in den Warenkorb gelegt" eventually
    And I should see "10" eventually
    When I click on "Warenkorb bearbeiten"
    Then I should see "Einfacher Artikel" eventually
    And I should see "10,00" eventually
    When I am on the detail page for article with ordernumber "SW88888"
    And I put the current article "2" times into the basket
    Then I should see "21,00" eventually
    When I am on the detail page for article with ordernumber "SW88888"
    And I put the current article "7" times into the basket
    Then I should see "50,00" eventually
