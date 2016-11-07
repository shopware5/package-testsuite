@checkout @customergroups @javascript
Feature: Checkout articles (scenario origin is account without articles in basket)

  Background: I can login as a user with correct credentials
    Given the following customer groups exist:
      | key | name    | tax | taxInput | mode | discount | minimumOrder | minimumOrderSurcharge |
      | H   | Händler | 0   | 0        | 0    | 0        | 0            | 0                     |
    And the customer account "democustomer@example.com" exists
    And the customer account "mustermann@b2b.de" with group "H" exists
    And the following products exist in the store:
      | number  | name                         | price | supplier            | categories                                                             |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | Bienenstock         | Root > Deutsch > Nahrungsmittel > Süß                                  |
      | SWT0002 | Sushi-Reis                   | 12    | KendalJP Inc.       | Root > Deutsch > Nahrungsmittel > Getreide                             |
      | SWT0003 | Sommerhandschuhe "Sansibar"  | 35.50 | Kunstschneerasen AG | Root > Deutsch > Kleidung > Herren ; Root > Deutsch > Kleidung > Damen |

  @login
  Scenario Outline: I can login, add articles to basket and finish my order
    Given I am logged in with account "<email>"

    When  I am on the detail page for article with ordernumber "SWT0001"
    Then  I should see "BienenhoniK - Karl Süßkleber"

    When  I put the current article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "<sum>"
    And   the aggregations should look like this:
      | label         | value           |
      | sum           | <sum>           |
      | shipping      | <shipping>      |
      | total         | <total>         |
      | sumWithoutVat | <sumWithoutVat> |
      | 19 %          | <tax>           |

    When  I proceed to order confirmation
    And   I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | email                    | sum     | shipping | total   | sumWithoutVat | tax    |
      | democustomer@example.com | 15,60 € | 3,90 €   | 19,50 € | 16,39 €       | 3,11 € |
      | mustermann@b2b.de        | 13,11 € | 3,28 €   | 19,50 € | 16,39 €       | 3,11 € |

  @registration
  Scenario Outline: I can register, add articles to basket and finish my order
    Given there is no customer registered with e-mail address "<email>"
    And I register me:
      | field         | register[personal] | register[billing] |
      | customer_type | <customer_type>    |                   |
      | salutation    | mr                 |                   |
      | firstname     | Max                |                   |
      | lastname      | Mustermann         |                   |
      | email         | <email>            |                   |
      | password      | shopware           |                   |
      | company       |                    | Muster GmbH       |
      | street        |                    | Musterstr. 55     |
      | zipcode       |                    | 55555             |
      | city          |                    | Musterhausen      |
      | country       |                    | Deutschland       |
    Then  I should see "Willkommen, Max Mustermann"

    When  I am on the detail page for article with ordernumber "SWT0002"
    Then  I should see "Sushi-Reis"

    When  I put the current article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "36,00 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 36,00 € |
      | shipping      | 3,90 €  |
      | total         | 39,90 € |
      | sumWithoutVat | 33,53 € |
      | 19 %          | 6,37 €  |

    When  I proceed to order confirmation
    And   I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | customer_type | email                                                |
      | private       | frontend-checkout-account-private@example.localhost  |
      | business      | frontend-checkout-account-business@example.localhost |