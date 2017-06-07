@checkout @javascript @knownFailing
Feature: Checkout articles in a subshop

  Background:
    Given the following products exist in the store:
      | number  | name                         | price | supplier      | categories                                 |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | Bienenstock   | Root > Deutsch > Nahrungsmittel > Süß      |
      | SWT0002 | Sushi-Reis                   | 12    | KendalJP Inc. | Root > Deutsch > Nahrungsmittel > Getreide |
    And the following subshops exist:
      | name     | url       |
      | SubShop1 | /subshop1 |
    And the following customer Accounts exist:
      | email            |
      | test@example.com |

  Scenario: I can place an order in a subshop
    Given I am in subshop with URL "/subshop1"
    And I am logged in with account "test@example.com"
    And the cart contains the following products:
      | number  | name                         | quantity |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 3        |
    And the cart should contain 1 articles with a value of "15,60 €"
    When I proceed to checkout cart
    And I change the shipping method in checkout to "Standard Versand"
    Then   the aggregations should look like this:
      | label         | value   |
      | sum           | 15,60 € |
      | shipping      | 3,90 €  |
      | total         | 19,50 € |
      | sumWithoutVat | 16,39 € |
    When I am on the page "CheckoutCart"
    And I add the article "SWT0002" to my basket
    Then the cart should contain 2 articles with a value of "27,60 €"
    And the aggregations should look like this:
      | label         | value   |
      | sum           | 27,60 € |
      | shipping      | 3,90 €  |
      | total         | 31,50 € |
      | sumWithoutVat | 26,47 € |
      | 19 %          | 5,03 €  |

    When I remove the article on position 1
    Then the cart should contain 1 articles with a value of "12,00 €"
    And the aggregations should look like this:
      | label         | value   |
      | sum           | 12,00 € |
      | shipping      | 3,90 €  |
      | total         | 15,90 € |
      | sumWithoutVat | 13,36 € |
      | 19 %          | 2,54 €  |

    When  I proceed to order confirmation
    And  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"