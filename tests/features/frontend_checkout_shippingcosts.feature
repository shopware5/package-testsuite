@javascript @frontendCheckout
Feature: I can place an order with an existing account

  Background:
    Given the following products exist in the store:
      | number  | name               | price | supplier  | categories                               |
      | SWT0005 | Quantum Buster 2k9 | 49,99 | Acme Inc. | Root > Deutsch > Trickfilm > Gefahrengut |
    And the following shipping options exist:
      | name             | costs | calculationType | shippingType        | surchargeCalculation          |
      | Standard Versand | 3,9   | Preis           | Standard Versandart | Als eigene Warenkorb-Position |
    And the payment method "Vorkasse" does not have risk management rules and has a surcharge of "0"

  Scenario: MwSt calculation with default setup
    Given the cart contains the following products:
      | number  | name               | quantity |
      | SWT0005 | Quantum Buster 2k9 | 1        |
    And the shipping method "Standard Versand" has the following shipping costs:
      | from | to       | costs |
      | 0    | beliebig | 3,9   |
    Then the cart should contain 1 articles with a value of "49,99 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 49,99 € |
      | shipping      | 3,90 €  |
      | total         | 53,89 € |
      | sumWithoutVat | 45,29 € |

  @shipping @mwst
  Scenario: MwSt calculation with free shipping
    Given the following shipping options exist:
      | name                  | costs | calculationType | shippingType        | surchargeCalculation | shippingfree |
      | Shipping Free from 40 | 3,9   | Preis           | Standard Versandart | Immer berechnen      | 40           |
    And the shipping method "Shipping Free from 40" has the following shipping costs:
      | from  | to       | costs |
      | 0     | 40       | 3,9   |
      | 40,01 | beliebig | 0     |
    And the shipping method "Shipping Free from 40" is active for the following payment methods:
      | name        |
      | Rechnung    |
      | Lastschrift |
      | Vorkasse    |
      | Nachnahme   |
      | SEPA        |
    And the shipping method "Shipping Free from 40" is active for the following countries:
      | name        |
      | Deutschland |
    And the cart contains the following products:
      | number  | name               | quantity |
      | SWT0005 | Quantum Buster 2k9 | 1        |
    And I change the dispatch method in cart to "Shipping Free from 40"
    Then the cart should contain 1 articles with a value of "49,99 €"
    And the aggregations should look like this:
      | label         | value   |
      | sum           | 49,99 € |
      | shipping      | 0,00 €  |
      | total         | 49,99 € |
      | sumWithoutVat | 42,01 € |

  @shipping @mwst @surcharge
  Scenario: MwSt calculation with inclusive surcharges and free shipping
    Given the following shipping options exist:
      | name                  | costs | calculationType | shippingType        | surchargeCalculation | shippingfree |
      | Shipping Free from 40 | 3,9   | Preis           | Standard Versandart | Immer berechnen      | 40           |
    And the shipping method "Shipping Free from 40" has the following shipping costs:
      | from  | to       | costs |
      | 0     | 40       | 3,9   |
      | 40,01 | beliebig | 0     |
    And the shipping method "Shipping Free from 40" is active for the following payment methods:
      | name        |
      | Rechnung    |
      | Lastschrift |
      | Vorkasse    |
      | Nachnahme   |
      | SEPA        |
    And the shipping method "Shipping Free from 40" is active for the following countries:
      | name        |
      | Deutschland |
    And the payment method "Vorkasse" does not have risk management rules and has a surcharge of "5"
    And the cart contains the following products:
      | number  | name               | quantity |
      | SWT0005 | Quantum Buster 2k9 | 1        |
    And I change the dispatch method in cart to "Shipping Free from 40"
    Then the cart should contain 1 articles with a value of "49,99 €"
    And the aggregations should look like this:
      | label         | value   |
      | sum           | 49,99 € |
      | shipping      | 5,00 €  |
      | total         | 54,99 € |
      | sumWithoutVat | 46,21 € |