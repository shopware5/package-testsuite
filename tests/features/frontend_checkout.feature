@javascript @frontendCheckout
Feature: I can place an order with an existing account

  Background:
    Given the following products exist in the store:
      | number  | name                         | price | supplier            | categories                                                             |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | Bienenstock         | Root > Deutsch > Nahrungsmittel > Süß                                  |
      | SWT0002 | Sushi-Reis                   | 12    | KendalJP Inc.       | Root > Deutsch > Nahrungsmittel > Getreide                             |
      | SWT0003 | Sommerhandschuhe "Sansibar"  | 35.50 | Kunstschneerasen AG | Root > Deutsch > Kleidung > Herren ; Root > Deutsch > Kleidung > Damen |
    And the following countries are active for checkout:
      | iso | shippingFree | taxFree | taxFreeUstId | active | displayStateInRegistration | forceStateInRegistration |
      | CH  | false        | 1       | 1            | 1      | 1                          | 0                        |
    And the customer account "democustomer@example.com" exists
    And I am logged in with account "democustomer@example.com"

  Scenario Outline: I can navigate to a product
    Given I am on the homepage
    When I navigate to category tree "<categoryTree>"
    Then I should be able to see the product '<productName>' with price "<price>"
    Examples:
      | categoryTree              | productName                  | price |
      | Nahrungsmittel > Süß      | BienenhoniK - Karl Süßkleber | 5.20  |
      | Nahrungsmittel > Getreide | Sushi-Reis                   | 12    |
      | Kleidung > Damen          | Sommerhandschuhe "Sansibar"  | 35.50 |
      | Kleidung > Herren         | Sommerhandschuhe "Sansibar"  | 35.50 |

  Scenario: I can put articles to the basket, check all prices and pay via C.O.D. service
    Given the cart contains the following products:
      | number  | name                         | quantity |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 3        |
    Then the cart should contain 1 articles with a value of "15,60 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 15,60 € |
      | shipping      | 3,90 €  |
      | total         | 19,50 € |
      | sumWithoutVat | 16,39 € |
    When  I add the article "SWT0002" to my basket
    Then  the cart should contain 2 articles with a value of "27,60 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 27,60 € |
      | shipping      | 3,90 €  |
      | total         | 31,50 € |
      | sumWithoutVat | 26,47 € |
      | 19 %          | 5,03 €  |

    When  I remove the article on position 1
    Then  the cart should contain 1 articles with a value of "12,00 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 12,00 € |
      | shipping      | 3,90 €  |
      | total         | 15,90 € |
      | sumWithoutVat | 13,36 € |
      | 19 %          | 2,54 €  |

    When  I proceed to order confirmation
    And  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"


  @shipping @payment
  Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
    Given the payment type "Rechnung" does not have risk management rules and has a surcharge of "5"
    And the cart contains the following products:
      | number  | name                         | quantity |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 3        |
      | SWT0003 | Sommerhandschuhe "Sansibar"  | 2        |
    Then the cart should contain 2 articles with a value of "86,60 €"
    When I proceed to order confirmation
    And   I change my shipping address:
      | field   | address |
      | country | Schweiz |
    Then  the aggregations should look like this:
      | label    | value   |
      | sum      | 72,77 € |
      | shipping | 3,28 €  |
      | total    | 76,05 € |
    And   I should not see "MwSt."

    When  I change my shipping address:
      | field   | address     |
      | country | Deutschland |
    Then  the aggregations should look like this:
      | label    | value   |
      | shipping | 3,90 €  |
      | total    | 90,5 €  |
      | 19 %     | 14,45 € |

    When  I change the payment method in checkout to "Rechnung"
    Then  the current payment method should be "Rechnung"
    And   I should see "Zuschlag für Zahlungsart"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 91,60 € |
      | shipping      | 3,90 €  |
      | total         | 95,50 € |
      | sumWithoutVat | 80,25 € |
      | 19 %          | 15,25 €  |
    And   I should see "AGB und Widerrufsbelehrung"

    When  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

