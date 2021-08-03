@javascript @productexport
Feature: I can use product exports

  Background:

    Given the following products exist in the store:
      | number  | name      | price  | supplier     | categories                                        |
      | SWT0001 | Product A | 5.20   | Supplier I   | Root > Deutsch > ErsteKategorie > Unterkategorie  |
      | SWT0002 | Product B | 12     | Supplier I   | Root > Deutsch > ZweiteKategorie > Unterkategorie |
      | SWT0003 | Product C | 35.50  | Supplier II  | Root > Deutsch > ZweiteKategorie > Unterkategorie |
      | SWT0004 | Product D | 135.50 | Supplier III | Root > Deutsch > ZweiteKategorie > Unterkategorie |

  # Test Profile 01 - Basic export with modifiers and shipping costs
  @isolated
  Scenario: I can create and use a basic product export (Profile 1)
    Given I am logged into the backend
    When I hover backend menu item "Marketing"
    And I click the "Produktexporte" menu element
    Then I should see "Letzter Export" eventually
    And I click the "Hinzufügen" button
    And I fill in the product export configuration:
      | label                  | value                        | type       |
      | Titel:                 | Profile 01                   | input      |
      | Dateiname:             | Profile01                    | input      |
      | Aktiv:                 | true                         | checkbox   |
      | Cache-Zeit / Methode:  | Live                         | combobox   |
      | Shop:                  | Demo shop                    | combobox   |
      | Kundengruppe:          | Shopkunden                   | combobox   |
      | Kategorie:             | Deutsch                      | selecttree |
      | Varianten exportieren: | Nein                         | combobox   |
      | Dateiformat:           | TXT mit Tab als Trennzeichen | combobox   |
    And I click on the "Template" tab
    And I enter the template
    """
      {strip}
      {$sArticle.ordernumber|escape}{#S#}
      {$sArticle.supplier|escape}{#S#}
      {$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}
      {$sArticle.price|escape:"number"}{#S#}
      DE::DHL:{$sArticle|@shippingcost:"prepayment":"de"}{#S#}
    """
    And I click the "Speichern und schließen" button
    Then I should see "Profile 01" eventually

    When I open the "Profile 01" export file
    Then it should contain the following product data:
      """
      SWT0001 Supplier I Product A 5,20 DE::DHL:3.9    SWT0002 Supplier I Product B 12,00 DE::DHL:3.9    SWT0003 Supplier II Product C 35,50 DE::DHL:3.9    SWT0004 Supplier III Product D 135,50 DE::DHL:3.9
      """

  ## Test Profile 02 - Basic export with modifiers and shipping costs
  @isolated
  Scenario: I can create and use an advanced product export (Profile 2)
    Given I am logged into the backend

    When I am on the page "ProductExportModule"
    And I click the "Hinzufügen" button
    And I fill in the product export configuration:
      | label                  | value                        | type       |
      | Titel:                 | Profile 02                   | input      |
      | Dateiname:             | Profile02                    | input      |
      | Aktiv:                 | true                         | checkbox   |
      | Cache-Zeit / Methode:  | Live                         | combobox   |
      | Shop:                  | Demo shop                    | combobox   |
      | Kundengruppe:          | Shopkunden                   | combobox   |
      | Kategorie:             | Deutsch                      | selecttree |
      | Varianten exportieren: | Nein                         | combobox   |
      | Dateiformat:           | TXT mit Tab als Trennzeichen | combobox   |
    And I click on the "Template" tab
    And I enter the template
    """
      {strip}
      {$sArticle.ordernumber|escape}{#S#}
      {$sArticle.supplier|escape}{#S#}
      {$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}
      {$sArticle.price|escape:"number"}{#S#}
      DE::DHL:{$sArticle|@shippingcost:"prepayment":"de"}{#S#}
    """
    And I click on the "Hersteller-Filter" tab
    And I block products from supplier "Supplier I"
    And I click on the "Weitere  Filter" tab
    And I define a minimum price filter with a value of 15
    And I click the "Speichern und schließen" button
    Then I should see "Profile 02" eventually

    When I open the "Profile 02" export file
    Then it should contain the following product data:
      """
      SWT0003 Supplier II Product C 35,50 DE::DHL:3.9    SWT0004 Supplier III Product D 135,50 DE::DHL:3.9
      """

    When I am on the page "ProductExportModule"
    Then I should see "Profile 02" eventually
    When I click the edit icon on the export "Profile 02"
    And I fill in the product export configuration:
      | label        | value | type     |
      | Dateiformat: | CSV   | combobox |
    And I click the "Speichern und schließen" button
    Then I should see "Profile 02" eventually

    When I open the "Profile 02" export file
    Then it should contain the following product data:
      """
      "SWT0003";"Supplier II";&quot;Product C&quot;;35,50;DE::DHL:3.9;   "SWT0004";"Supplier III";&quot;Product D&quot;;135,50;DE::DHL:3.9;
      """

     # Test Profile 03 - Enter invalid template and see if the shop crashes
  @isolated
  Scenario: I can create an invalid product export without causing any issues for the shop (Profile 03)
    Given I am logged into the backend

    When I am on the page "ProductExportModule"
    And I click the "Hinzufügen" button
    And I fill in the product export configuration:
      | label                  | value                        | type       |
      | Titel:                 | Profile 03                   | input      |
      | Dateiname:             | Profile03                    | input      |
      | Aktiv:                 | true                         | checkbox   |
      | Cache-Zeit / Methode:  | Live                         | combobox   |
      | Shop:                  | Demo shop                    | combobox   |
      | Kundengruppe:          | Shopkunden                   | combobox   |
      | Kategorie:             | Deutsch                      | selecttree |
      | Varianten exportieren: | Nein                         | combobox   |
      | Dateiformat:           | TXT mit Tab als Trennzeichen | combobox   |
    And I click on the "Template" tab
    And I enter the template
    """
      {strip}
      {$sArticle.ordernumber|escape{#S#}
      {$sArticle.suppler|escape}{#S#}
      {$sArticle.name|striptags|strip|truncate:80:"...":true|escape|tmlentities}{#S#}
      {$sArticle.price|escape:"number"}{#S#}
      DE::DHL:{$sArticle|@shippigcost:"prepayment":"de"}{#S#}
    """
    And I click the "Speichern und schließen" button
    Then I should see "Profile 03" eventually

    When I open the "Profile 03" export file
    Then I should see "Ups! Ein Fehler ist aufgetreten!" eventually
    And I should be able to access the shop via using "http://shopware.test/"
    And I should see "Newsletter" eventually