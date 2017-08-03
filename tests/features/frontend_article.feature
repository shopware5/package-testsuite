@javascript @backend @article
Feature: I can create, update and delete an article

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists

  Scenario: I can create a new article
    Given I am on the page "BackendLogin"
    When I log in with user "demo" and password "demo"
    Then I should see "Artikel" eventually
    And I should see "Feedback" eventually
    When I hover backend menu item "Artikel"
    And I click the "(STRG + ALT + N)" extended menu element
    Then I should see "Artikeldetails : Neuer Artikel" eventually

    When I fill in the basic configuration:
      | label                | value          | type       | action | fieldset   |
      | Hersteller:          | Finch          | comboinput |        | Stammdaten |
      | Artikel-Bezeichnung: | Erster Artikel | input      |        | Stammdaten |
      | Artikelnummer:       | SW10001        | input      |        | Stammdaten |
    And I set "10" as the article price
    And I choose "Eine kurze Beschreibung." as article description
    Then I am be able to save my article

    When I click on the "Kategorien" tab
    Then I should see "Zugewiesene Kategorien" eventually
    When I expand the "Deutsch" element
    Then I should see "ErsteKategorie" eventually
    When I expand the "ErsteKategorie" element
    Then I should see "Unterkategorie" eventually

    When I expand the "Unterkategorie" element
    When I click the "Plus" icon to add "Unterkategorie"
    Then I should find "Unterkategorie" in the area "Zugewiesene Kategorien"
    And I am be able to save my article

    When I click on the "Stammdaten" tab
    When I set "Demo shop" as the shop for the preview
    And I start the preview
    Then I check if my article data is displayed:
      | info    |
      | SW10001 |
      | 10,00   |