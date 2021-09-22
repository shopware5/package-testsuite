@javascript @backend @article @variants
Feature: I can generate and use article variants

  Background:
    Given the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists

  Scenario: I can create a new article with variants
    Given I am logged into the backend
    Then I should see "Artikel" eventually
    And I should see "Feedback" eventually
    When I hover backend menu item "Artikel"
    And I click on backend menu item that contains "Anlegen"
    Then I should see "Artikeldetails : Neuer Artikel" eventually

    When I fill in the basic configuration:
      | label                | value          | type       | fieldset   |
      | Hersteller:          | Finch          | comboinput | Stammdaten |
      | Artikel-Bezeichnung: | Erster Artikel | input      | Stammdaten |
      | Artikelnummer:       | SW99999        | input      | Stammdaten |
      | Varianten-Artikel:   | true           | checkbox   | Stammdaten |
    And I set "10" as the article price
    Then I am able to save my article
    And I should see "Erfolgreich" eventually
    And the "Varianten" tab should be active

    When I click on the "Varianten" tab
    Then I should see "Art des Konfigurators:" eventually
    And I should see "Gruppe erstellen:" eventually
    When I create the "Farbe" group via "Gruppe erstellen:"
    And I click the "Erstellen und Aktivieren" button
    Then I should see "Farbe" eventually
    And the group "Farbe" should be listed in the area "Aktive Gruppen"
    When I click "Farbe" to create the options of it
    And I create the following options options:
      | option |
      | Rot    |
      | Gelb   |
      | Blau   |
    Then the option "Blau" should be listed in the area "Aktive Optionen"

    When I click the "Varianten generieren" button
    And I click the "Ja" button
    Then I should see "Abgleichen" eventually
    When I click the "Vorgang starten" button
    Then I should see "Erfolg" eventually

    When I open inline editing of variant "SW99999.1" and add "test"
    Then I should see "Erfolg" eventually
    Then I should see "SW99999.1test" eventually

    When I open variant detail page of variant "SW99999.1test"
    Then I should see "Konfiguratoroptionen" eventually
    When I click the "Artikel speichern" button
    Then I should see "Erfolg" eventually

    When I click on the "Kategorien" tab
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
    Then I should see "Farbe" eventually
    When I choose the variant with the number "3"
    Then I should see "Blau" eventually
    And I wait for the loading indicator to disappear
    When I put the current article "1" times into the basket
    Then I should see "Der Artikel wurde erfolgreich in den Warenkorb gelegt" eventually
    And I should see "Erster Artikel Blau" eventually
