@javascript @knownFailing
Feature: I can do stuff in the backend

  Background:
    Given I am on the page "backend index"
    And I am logged in with user "demo" and password "demo"

  Scenario: I can allow api access
    When I hover backend menu item "Einstellungen"
    Then I should see "Benutzerverwaltung" eventually
    When I click on backend menu item that contains "Benutzerverwaltung"
    Then I should see "Backend Benutzer Administration" eventually
    And  I should see "demo@demo.de" eventually
    When I edit the user with email "demo@demo.de"
    Then I should see "Benutzer hinzufügen/editieren" eventually
    And I should see "API-Zugang" eventually
    When I activate the API access for the user with e-mail "demo@demo.de"
    Then I should see "Erfolgreich" eventually

  Scenario: I can create a new Manufacturer via backend
    When I hover backend menu item "Artikel"
    Then I should see "Hersteller" eventually
    When I click on backend menu item "Hersteller"
    Then I should see "Hersteller Verwaltung" eventually
    When I create a new manufacturer with the following data:
      | field       | value                                                                                                                             |
      | name        | Lorem Ipsum AG                                                                                                                    |
      | pageTitle   | Die Lorem Ipsum AG                                                                                                                |
      | url         | http://shopware.com                                                                                                               |
      | description | Die Lorem Ipsum AG ist eine fiktive Firma, die der Shopware AG bei deren Package Tests seit 2016 als Platzhalter zur Seite steht. |
    Then I should see "Hersteller erfolgreich gespeichert." eventually

  Scenario Outline: I can create a bunch of articles via backend
    When I hover backend menu item "Artikel"
    Then I should see "Anlegen" eventually
    When I click on backend menu item that contains "(STRG + ALT + N)"
    Then I should see "Artikeldetails : Neuer Artikel" eventually
    When I create a new article with the following data:
      | field        | value         |
      | manufacturer | Example       |
      | name         | <name>        |
      | ek           | <ek>          |
      | price        | <price>       |
      | pseudoPrice  | <pseudoPrice> |
      | description  | <description> |
      | image        | <imageName>   |
    Then I should see "wurde gespeichert" eventually
    Examples:
      | name                           | ek | price | pseudoPrice | description                                | imageName                               |
      | Klobürstenhalter Chrome-Valley | 12 | 24    | 30          | Keiner hält, was er verspricht? Der schon. | feature_create_article_ChromeValley.jpg |