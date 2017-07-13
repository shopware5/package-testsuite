@javascript @checkout @frontend
Feature: I can buy products using the store frontend

  Background:

    Given the following products exist in the store:
      | number  | name                         | price | supplier                | categories                                                             |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | Bienenstock             | Root > Deutsch > Nahrungsmittel > Süß                                  |
      | SWT0002 | Sushi-Reis                   | 12    | KendalJP Inc.           | Root > Deutsch > Nahrungsmittel > Getreide                             |
      | SWT0003 | Sommerhandschuhe "Sansibar"  | 35.50 | Kunstschneerasen AG     | Root > Deutsch > Kleidung > Herren ; Root > Deutsch > Kleidung > Damen |
      | SWT0004 | Kaviar vom Rind              | 44,99 | GenTech? Schmeckt! GmbH | Root > Deutsch > Nahrungsmittel > Ungewöhnlich                         |

    And the following countries are active for checkout:
      | iso | shippingFree | taxFree | taxFreeUstId | active | displayStateInRegistration | forceStateInRegistration |
      | CH  | false        | 1       | 1            | 1      | 1                          | 0                        |

    And the following customer accounts exist:
      | email                             | password | group | country |
      | regular.customer@shopware.de.test | shopware |       | DE      |
      | b2b.customer@shopware.de.test     | shopware | H     | DE      |
      | regular.customer@shopware.ch.test | shopware |       | CH      |
      | b2b.customer@shopware.ch.test     | shopware | H     | CH      |


  ##
  # Unregistered user puts products in cart and chooses free shipping method
  #
  # Tested functionality:
  #   - Adding products to cart
  #   - Free shipping methods (min value based)
  #   - Creating shipping methods in the backend
  #   - Basic cart calculation
  #
  Scenario: My cart can handle free shipping methods

    Given the following shipping options exist:
      | name                  | costs | calculationType | shippingType        | surchargeCalculation | shippingfree | activePaymentMethods | activeCountries |
      | Shipping Free from 40 | 3,9   | Preis           | Standard Versandart | Immer berechnen      | 40           | Rechnung, Vorkasse   | Deutschland     |

    And I am not logged in

    And the shipping method "Shipping Free from 40" has the following shipping costs:
      | from  | to       | costs |
      | 0     | 40       | 3,9   |
      | 40,01 | beliebig | 0     |

    And the cart contains the following products:
      | number  | name            | quantity |
      | SWT0004 | Kaviar vom Rind | 1        |

    And I change the dispatch method in cart to "Shipping Free from 40"

    Then the cart should contain 1 articles with a value of "44,99 €"
    And the aggregations should look like this:
      | label         | value   |
      | sum           | 44,99 € |
      | shipping      | 0,00 €  |
      | total         | 44,99 € |
      | sumWithoutVat | 37,81 € |

    When I change the dispatch method in cart to "Standard Versand"
    Then the aggregations should look like this:
      | label         | value   |
      | sum           | 44,99 € |
      | shipping      | 3,90 €  |
      | total         | 48,89 € |
      | sumWithoutVat | 41,09 € |

    Then I delete the shipping method "Shipping Free from 40"

  ##
  # New user registers, adds products to cart and completes checkout
  #
  # Tested functionality:
  #   - Registering
  #   - Adding products to cart
  #   - Completing Checkout
  #   - Basic cart calculation
  #   - Swiss and German customers (incl. and excl. VAT)
  #
  # Scenario Variables:
  #   - Regular customers and business customers
  #
  Scenario Outline: I can register and order products
    Given there is no customer registered with e-mail address "<email>"
    And I register myself:
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
      | country       |                    | <country>         |

    Then  I should see "Willkommen, Max Mustermann"

    When  I am on the detail page for article with ordernumber "SWT0004"
    Then  I should see "Kaviar vom Rind"

    When  I put the current article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "<sum>"
    And   the aggregations should look like this:
      | label    | value      |
      | sum      | <sum>      |
      | shipping | <shipping> |
      | total    | <total>    |

    When  I proceed to order confirmation
    And   I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | customer_type | email                                  | country     | sum      | shipping | total    |
      | private       | regular.new-customer@shopware.de.test  | Deutschland | 134,97 € | 3,90 €   | 138,87 € |
      | business      | business.new-customer@shopware.de.test | Deutschland | 134,97 € | 3,90 €   | 138,87 € |
      | private       | regular.new-customer@shopware.ch.test  | Schweiz     | 113,43 € | 3,28 €   | 116,71 € |
      | business      | business.new-customer@shopware.ch.test | Schweiz     | 113,43 € | 3,28 €   | 116,71 € |

  ##
  # Already logged-in user puts items into cart and completes checkout
  #
  # Tested functionality:
  #   - Adding products to cart
  #   - Completing Checkout
  #   - Basic cart calculation
  #
  # Scenario Variables:
  #   - Regular customers and business customers
  #   - Carts w/ and w/o VAT (Germany and Swiss customers)
  #   - Prepayment and invoice payment methods
  #
  Scenario Outline: I can order products while I'm logged in:
    Given I am logged in with account "<account.email>" with password "<account.password>"
    And the following payment methods are activated:
      | name     |
      | Rechnung |
      | Vorkasse |

    And the cart contains the following products:
      | number                | quantity                |
      | <cart.product.number> | <cart.product.quantity> |

    Then the cart should contain 1 articles with a value of "<cart.sum>"
    And the aggregations should look like this:
      | label    | value           |
      | sum      | <cart.sum>      |
      | shipping | <cart.shipping> |
      | total    | <cart.total>    |

    When I proceed to order confirmation
    And I change the payment method in checkout to "<payment>"
    And I proceed to checkout
    Then I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | account.email                     | account.password | cart.product.number | cart.product.quantity | payment  | cart.sum | cart.shipping | cart.total |
      | regular.customer@shopware.de.test | shopware         | SWT0001             | 3                     | Rechnung | 15,60 €  | 3,90 €        | 19,50 €    |
      | b2b.customer@shopware.de.test     | shopware         | SWT0001             | 3                     | Vorkasse | 15,60 €  | 3,90 €        | 19,50 €    |
      | regular.customer@shopware.ch.test | shopware         | SWT0001             | 3                     | Vorkasse | 13,11 €  | 3,28 €        | 16,39 €    |
      | b2b.customer@shopware.ch.test     | shopware         | SWT0001             | 3                     | Rechnung | 13,11 €  | 3,28 €        | 16,39 €    |

  ##
  # Already logged-in, German customer (incl. VAT) adds & removes item from cart before completing checkout
  #
  # Tested functionality:
  #   - Adding and removing products from cart
  #   - Basic cart calculations
  #   - German VAT calculations
  #
  Scenario: I can add and remove products from the cart while logged in as a regular, German customer:
    Given I am logged in with account "regular.customer@shopware.de.test " with password "shopware"
    And the cart contains the following products:
      | number  | name       | quantity |
      | SWT0002 | Sushi-Reis | 2        |

    Then the cart should contain 1 articles with a value of "24,00 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 24,00 € |
      | shipping      | 3,90 €  |
      | total         | 27,90 € |
      | sumWithoutVat | 23,45 € |
      | 19 %          | 4,45 €  |

    When  I add the article "SWT0001" to my basket
    Then  the cart should contain 2 articles with a value of "29,20 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 29,20 € |
      | shipping      | 3,90 €  |
      | total         | 33,10 € |
      | sumWithoutVat | 27,82 € |
      | 19 %          | 5,28 €  |

    When  I remove the article on position 1
    And  I add the article "SWT0001" to my basket
    Then  the cart should contain 1 articles with a value of "10,40 €"
    And   the aggregations should look like this:
      | label         | value   |
      | sum           | 10,40 € |
      | shipping      | 3,90 €  |
      | total         | 14,30 € |
      | sumWithoutVat | 12,02 € |
      | 19 %          | 2,28 €  |

    When  I proceed to order confirmation
    And  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

  ##
  # Already logged-in, Swiss customer (no VAT) adds & removes item from cart before completing checkout
  #
  # Tested functionality:
  #   - Adding and removing products from cart
  #   - Basic cart calculations
  #   - Net cart calculations
  #
  Scenario: I can add and remove products from the cart while logged in as a regular, Swiss customer:
    Given I am logged in with account "regular.customer@shopware.ch.test " with password "shopware"
    And the cart contains the following products:
      | number  | name       | quantity |
      | SWT0002 | Sushi-Reis | 2        |

    # Technically, this should be "20,17€", but there appears to be a rounding error
    Then the cart should contain 1 articles with a value of "20,16 €"
    And   the aggregations should look like this:
      | label    | value   |
      | sum      | 20,16 € |
      | shipping | 3,28 €  |
      | total    | 23,44 € |

    When  I add the article "SWT0001" to my basket
    Then  the cart should contain 2 articles with a value of "24,53 €"
    And   the aggregations should look like this:
      | label    | value   |
      | sum      | 24,53 € |
      | shipping | 3,28 €  |
      | total    | 27,81 € |

    When  I remove the article on position 1
    And  I add the article "SWT0001" to my basket
    And  I add the article "SWT0001" to my basket
    Then  the cart should contain 1 articles with a value of "13,11 €"
    And   the aggregations should look like this:
      | label    | value   |
      | sum      | 13,11 € |
      | shipping | 3,28 €  |
      | total    | 16,39 € |

    When  I proceed to order confirmation
    And  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"


  ##
  # Already logged-in, German customer (incl. VAT) buys a product from its detail page
  #
  # Tested functionality:
  #   - Buying products from detail page
  #
  # Scenario variables:
  #   - Regular customer / Business customer
  #
  Scenario Outline: I can buy an article from its detail page and complete checkout
    Given I am logged in with account "<account.email>" with password "<account.password>"

    When  I am on the detail page for article with ordernumber "SWT0001"
    Then  I should see "BienenhoniK - Karl Süßkleber"

    When  I put the current article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "15,60€"
    And   the aggregations should look like this:
      | label         | value  |
      | sum           | 15,60€ |
      | shipping      | 3,90€  |
      | total         | 19,50€ |
      | sumWithoutVat | 16,39€ |
      | 19 %          | 3,11€  |

    When  I proceed to order confirmation
    And   I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | account.email                     | account.password |
      | regular.customer@shopware.de.test | shopware         |
      | b2b.customer@shopware.de.test     | shopware         |

  ##
  # Already logged-in, Swiss customer (no VAT) buys a product from its detail page
  #
  # Tested functionality:
  #   - Buying products from detail page
  #
  # Scenario variables:
  #   - Regular customer / Business customer
  #
  Scenario Outline: I can buy an article from its detail page and complete checkout
    Given I am logged in with account "<account.email>" with password "<account.password>"

    When  I am on the detail page for article with ordernumber "SWT0001"
    Then  I should see "BienenhoniK - Karl Süßkleber"

    When  I put the current article "3" times into the basket
    Then  the cart should contain 1 articles with a value of "13,11€"
    And   the aggregations should look like this:
      | label    | value  |
      | sum      | 13,11€ |
      | shipping | 3,28€  |
      | total    | 16,39€ |

    When  I proceed to order confirmation
    And   I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"

    Examples:
      | account.email                     | account.password |
      | regular.customer@shopware.ch.test | shopware         |
      | b2b.customer@shopware.ch.test     | shopware         |


  Scenario Outline: I can finish my order with different payment and delivery methods
    Given the following payment methods are activated:
      | name     |
      | Rechnung |
      | Vorkasse |

    And the following shipping options exist:
      | name            | costs | calculationType | shippingType        | surchargeCalculation          | activePaymentMethods | activeCountries      |
      | Express Versand | 9,9   | Preis           | Standard Versandart | Als eigene Warenkorb-Position | Rechnung, Vorkasse   | Deutschland, Schweiz |

    When I am logged in with account "<email>" with password "<password>"
    And the cart contains the following products:
      | number  | name            | quantity |
      | SWT0004 | Kaviar vom Rind | 1        |

    And I proceed to checkout cart
    And I change my payment method to "<paymentMethod>"
    And I change my shipping method to "<shippingMethod>"

    Then  the aggregations should look like this:
      | label    | value           |
      | shipping | <shippingCosts> |
      | total    | <totalSum>      |

    When  I proceed to checkout
    Then  I should see "Vielen Dank für Ihre Bestellung bei"
    Examples:
      | email                             | password | paymentMethod | shippingMethod   | shippingCosts | totalSum |
      | regular.customer@shopware.de.test | shopware | Rechnung      | Standard Versand | 3,90 €        | 48,89 €  |
      | b2b.customer@shopware.de.test     | shopware | Rechnung      | Express Versand  | 9,90 €        | 54,89 €  |
      | regular.customer@shopware.ch.test | shopware | Vorkasse      | Express Versand  | 8,32 €        | 46,13 €  |


