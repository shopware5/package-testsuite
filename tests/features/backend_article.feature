@javascript @backend @article
Feature: I can create, update and delete an article

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists

  Scenario: I can create a new article
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Anlegen"
    Then I should see "Artikeldetails : Neuer Artikel" eventually

    When I fill in the basic configuration:
      | label                | value          | type       | fieldset   |
      | Hersteller:          | Finch          | comboinput | Stammdaten |
      | Artikel-Bezeichnung: | Erster Artikel | input      | Stammdaten |
      | Artikelnummer:       | SW99999        | input      | Stammdaten |
    And I set "10" as the article price
    And I choose "Eine kurze Beschreibung." as article description
    And I click on the "Kategorien" tab
    Then I should see "Zugewiesene Kategorien" eventually

    When I expand the "Deutsch" element
    Then I should see "ErsteKategorie" eventually
    When I expand the "ErsteKategorie" element
    Then I should see "Unterkategorie" eventually

    When I expand the "Unterkategorie" element
    And I click to add the category with name "Unterkategorie" to the article
    Then I should find the category with name "Unterkategorie" in "Zugewiesene Kategorien"
    When I click on the "Stammdaten" tab
    Then I am able to save my article
    And I should see "Erfolgreich" eventually

    When I am on the homepage
    And I navigate to category tree "ErsteKategorie > Unterkategorie"
    Then I should be able to see the product "Erster Artikel" with price "10"
    When I am on the detail page for article with ordernumber "SW99999"
    And I put the current article "1" times into the basket
    Then I should see "Der Artikel wurde erfolgreich in den Warenkorb gelegt" eventually

  Scenario: I can edit an existing article
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Übersicht"
    Then I should see "SW99999" eventually
    When I click the edit icon of the entry "SW99999"
    Then I should see "Artikeldetails : Erster Artikel" eventually

    When I change the article name to "Erster Artikel EDIT"
    Then I am able to save my article
    And I should see "Erfolgreich" eventually
    When I am on the detail page for article with ordernumber "SW99999"
    Then I should see "Erster Artikel EDIT" eventually

  Scenario: I can delete an existing article
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Übersicht"
    Then I should see "SW99999" eventually
    When I click the delete icon of the entry "SW99999"
    And I confirm to delete the entry
    Then I should eventually not see "SW99999"
    When I am on the detail page for article with ordernumber "SW99999"
    Then I should see "Suchergebnis für SW99999" eventually
    And I should see "keine Artikel gefunden" eventually
