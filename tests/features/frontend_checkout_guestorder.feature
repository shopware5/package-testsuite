@javascript @checkout @frontend
Feature: I can buy products using the store frontend

  Background:
    Given the following products exist in the store:
      | number  | name                         | price | tax | supplier    | categories                            |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | 19  | Bienenstock | Root > Deutsch > Nahrungsmittel > Süß |

    Given the following shipping options exist:
      | name            | costs | calculationType | shippingType        | surchargeCalculation | shippingfree | activePaymentMethods | activeCountries |
      | Express Versand | 9,90  | Preis           | Standard Versandart | Immer berechnen      | 200          | SEPA, Vorkasse       | Deutschland     |

    And the following payment methods are activated:
      | name |
      | SEPA |

  Scenario Outline: I can order something without needing to register
    Given I am not logged in
    And I am on the detail page for article with ordernumber "SWT0001"
    And I put the current article "2" times into the basket

    When I am on the page "CheckoutCart"
    And I proceed to order confirmation
    And I fill in the registration form:
      | name                              | value                       | type     |
      | register[personal][customer_type] | Privatkunde                 | select   |
      | register[personal][salutation]    | mr                          | select   |
      | register[personal][firstname]     | David                       | input    |
      | register[personal][lastname]      | Bowie                       | input    |
      | register[personal][accountmode]   | 1                           | checkbox |
      | register[personal][email]         | no-account@shopware.de.test | input    |
      | register[billing][street]         | Drake Circus 1              | input    |
      | register[billing][zipcode]        | PL4 012                     | input    |
      | register[billing][city]           | Plymouth                    | input    |
      | register[billing][country]        | Deutschland                 | select   |

    And I click on "Weiter"
    And I change my shipping method to "<shippingMethod>"
    And I change my payment method to "<paymentMethod>"

    #And I click on payment method "<paymentMethod>"
    #And I click on shipping method "<shippingMethod>"

    Then the aggregations should look like this:
      | label         | value               |
      | sum           | 10,40 €             |
      | shipping      | <cart.shippingCost> |
      | total         | <cart.total>        |
      | sumWithoutVat | <cart.withoutVat>   |

    When I click on "Zur Kasse"
    And I check "sAGB"
    And I click on "Zahlungspflichtig bestellen"
    Then I should see "Vielen Dank für Ihre Bestellung" eventually

    When I am on the page "BackendLogin"
    And I log in with user "demo" and password "demo"
    And I am on the page "CustomerModule"
    Then I might need to close the welcome wizard
    When I click the edit icon on customer "David"
    Then I should see "Stammdaten" eventually
    And I should see "Schnellbestellungskonto" eventually

    Examples:
      | shippingMethod   | paymentMethod | cart.shippingCost | cart.total | cart.withoutVat |
      | Standard Versand | Vorkasse      | 3,90              | 14,30      | 12,02           |
      | Express Versand  | SEPA          | 9,90              | 20,30      | 17,06           |