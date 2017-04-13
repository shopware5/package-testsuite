@javascript @productexport
Feature: I can use product exports

  Background:
    Given the following products exist in the store:
      | number  | name                          | price | supplier            | categories                                       |
      | SWT0001 | BienenhoniK - Karl Sueskleber | 5.20  | Bienenstock         | Root > Deutsch > ErsteKategorie > Unterkategorie |
      | SWT0002 | Sushi-Reis                    | 12    | KendalJP Inc.       | Root > Deutsch > ErsteKategorie > Unterkategorie |
      | SWT0003 | Sommerhandschuhe              | 35.50 | Kunstschneerasen AG | Root > Deutsch > ErsteKategorie > Unterkategorie |
    And the following customer groups exist:
      | name       | key |
      | Shopkunden | EK  |
    And the category tree "Root > Deutsch > ErsteKategorie > Unterkategorie" exists


  Scenario: I can create and use a basic product export
    Given I am on the page "BackendLogin"
    When I log in with user "demo" and password "demo"
    Then I should see "Einstellungen" eventually
    And I should see "Feedback" eventually
    When I hover backend menu item "Marketing"
    And I click the "Produktexporte" menu element
    Then I should see "Letzter Export" eventually

    When I click the "Hinzufügen" element
    Then I should see "Feed - Konfiguration" eventually
    When I fill in the product export configuration:
      | label                  | value                  | type       | action          |
      | Titel:                 | Erster Test-Export     | input      |                 |
      | Dateiname:             | SwagFirstProductExport | input      |                 |
      | Aktiv:                 | true                   | checkbox   |                 |
      | Cache-Zeit / Methode:  | Live                   | combobox   | interval        |
      | Shop:                  | Demo shop              | combobox   | languageId      |
      | Kundengruppe:          | Shopkunden             | combobox   | customerGroupId |
      | Kategorie:             | Deutsch                | selecttree |                 |
      | Varianten exportieren: | Nein                   | combobox   | variantExport   |

    And I fill in the product export configuration:
      | label        | value                        | type     | action   |
      | Dateiformat: | TXT mit Tab als Trennzeichen | combobox | formatId |
    And I click on the "Template" tab
    Then I should be able to enter my basic template "{$sArticle|var_export}"

    When I click the "Speichern und schließen" element
    Then I should see "Erster Test-Export" eventually
    When I start the product export
    And I open the "Erster Test-Export" export file
    Then it should contain the following product data:
      | number  | name                          | price | supplier            |
      | SWT0001 | BienenhoniK - Karl Sueskleber | 5.20  | Bienenstock         |
      | SWT0002 | Sushi-Reis                    | 12    | KendalJP Inc.       |
      | SWT0003 | Sommerhandschuhe              | 35.50 | Kunstschneerasen AG |

