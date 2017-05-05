@checkout @javascript @knownFailing
Feature: Checkout articles (scenario origin is cart with one product in it)

  Background:
    Given the following products exist in the store:
      | number  | name               | price   | supplier           | categories                               |
      | SWT0005 | Quantum Buster 2k9 | 3989,99 | Acme Inc.          | Root > Deutsch > Trickfilm > Gefahrengut |
      | SWT0006 | Brise vom Nordkap  | 3,29    | Luftikus und Söhne | Root > Deutsch > Erfrischungen           |
    And the cart contains the following products:
      | number  | name               | quantity |
      | SWT0005 | Quantum Buster 2k9 | 1        |
    And the following customer Accounts exist:
      | email            |
      | test@example.com |
      | test@example.de  |
    And the following shipping options exist:
      | name            | costs | calculationType | shippingType        | surchargeCalculation          |
      | Express Versand | 9,9   | Preis           | Standard Versandart | Als eigene Warenkorb-Position |

  @fastOrder @payment @delivery
  Scenario Outline: I can finish my order with different payment and delivery methods
    Given the payment method "Rechnung" does not have risk management rules and has a surcharge of "5"
    Given the payment method "SEPA" does not have risk management rules
    And the shipping method "Express Versand" is active for the following payment methods:
      | name        |
      | Rechnung    |
      | Lastschrift |
      | Vorkasse    |
      | Nachnahme   |
      | SEPA        |
    And the shipping method "Express Versand" is active for the following countries:
      | name        |
      | Deutschland |
      | Schweiz     |
    And I am logged in with account "test@example.com"
    When I proceed to checkout cart
    And I change the payment method in checkout to "<paymentMethod>"
    And I change the shipping method in checkout to "<shippingMethod>"
    Then  the aggregations should look like this:
      | label    | value           |
      | shipping | <shippingCosts> |
      | total    | <totalSum>      |
    When  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"
    Examples:
      | paymentMethod | shippingMethod   | shippingCosts | totalSum  |
      | Rechnung      | Standard Versand | 3,90 €        | 3998,89 € |
      | Rechnung      | Express Versand  | 9,90 €        | 4004,89 € |