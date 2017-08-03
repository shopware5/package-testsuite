@javascript @frontend @voucher
Feature:

  Background:

  Scenario: I can create, edit and delete vouchers and my customers can use them
    Given I am on the page "BackendLogin"
    When I log in with user "demo" and password "demo"
    And I hover backend menu item "Marketing"
    And I click on backend menu item that contains "Gutscheine"
    Then I should see "Hinzufügen" eventually

    ## Create a new voucher
    When I click the "Hinzufügen" button
    Then I should see "Gutschein-Konfiguration" eventually

    When I fill out the voucher form:
      | label          | value                   | type  |
      | Beschreibung:  | Absoluter Testgutschein | input |
      | Mindestumsatz: | 10                      | input |
      | Bestellnummer: | TV001                   | input |
      | Code:          | PLYM17                  | input |
      | Wert:          | 4,55                    | input |

    And I click the "Speichern" button
    And I am on the page "VoucherModule"
    Then I should see "Absoluter Testgutschein" eventually

    ## Edit the voucher
    When I click the edit icon on the voucher named "Absoluter Testgutschein"
    Then I should see "Gutschein-Konfiguration" eventually

    When I fill out the voucher form:
      | label         | value                         | type  |
      | Beschreibung: | Neuer Absoluter Testgutschein | input |
      | Wert:         | 5,46                          | input |
    And I click the "Speichern" button
    And I am on the page "VoucherModule"
    Then I should see "Neuer Absoluter Testgutschein" eventually

    ## Using the voucher in the frontend
    Given the following customer accounts exist:
      | email                             | password | group | country |
      | regular.customer@shopware.de.test | shopware |       | DE      |

    And the following products exist in the store:
      | number  | name                         | price | tax | supplier    | categories                            |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | 19  | Bienenstock | Root > Deutsch > Nahrungsmittel > Süß |
    And I am logged in with account "regular.customer@shopware.de.test" with password "shopware"
    And the cart contains the following products:
      | name                         | number  | quantity | itemPrice | sum   |
      | BienenhoniK - Karl Süßkleber | SWT0001 | 4        | 5,2       | 20,80 |
    And I add the voucher "PLYM17" to my cart

    Then the aggregations should look like this:
      | label         | value   |
      | sum           | 15,34 € |
      | shipping      | 3,90 €  |
      | total         | 19,24 € |
      | sumWithoutVat | 16,17 € |

    ## Change voucher to from absolute to relative
    Given I am on the page "VoucherModule"
    And I click the edit icon on the voucher named "Neuer Absoluter Testgutschein"
    Then I should see "Gutschein-Konfiguration" eventually

    When I fill out the voucher form:
      | label         | value                         | type     |
      | Beschreibung: | Neuer Relativer Testgutschein | input    |
      | Abzug:        | Prozentual                    | combobox |
      | Wert:         | 10                            | input    |
      | Code:         | PLYM18                        | input    |
    And I click the "Speichern" button
    And I am on the page "VoucherModule"
    Then I should see "Neuer Relativer Testgutschein" eventually

    ## Use the voucher in the frontend
    Given I am on the page "CheckoutCart"
    And the cart contains the following products:
      | name                         | number  | quantity | itemPrice | sum   |
      | BienenhoniK - Karl Süßkleber | SWT0001 | 4        | 5,2       | 20,80 |
    And I add the voucher "PLYM18" to my cart
    Then the aggregations should look like this:
      | label         | value   |
      | sum           | 18,72 € |
      | shipping      | 3,90 €  |
      | total         | 22,62 € |
      | sumWithoutVat | 19,01 € |

    ## Delete the voucher
    Given I am on the page "VoucherModule"
    And I click the delete icon on the voucher named "Neuer Relativer Testgutschein"
    And I click the "Ja" button

  Scenario: I can create and use vouchers with individual codes
    Given I am on the page "BackendLogin"
    And I log in with user "demo" and password "demo"
    And I am on the page "VoucherModule"
    And I click the "Hinzufügen" button
    Then I should see "Gutschein-Konfiguration" eventually

    When I fill out the voucher form:
      | label                | value                             | type     |
      | Beschreibung:        | Neuer Individueller Testgutschein | input    |
      | Gutscheincode Modus: | Individuell                       | combobox |
      | Mindestumsatz:       | 10                                | input    |
      | Wert:                | 5                                 | input    |
      | Bestellnummer:       | PLYM19                            | input    |
    And I click the "Speichern" button
    And I wait for 2 seconds
    And I click on the "Individuelle Gutscheincodes" tab
    And I click the "Neue Codes generieren" button
    Then I should see "Nein" eventually
