@javascript @detail @frontend
Feature: I can navigate to a product

  Background:
    Given the following products exist in the store:
      | number  | name                         | price | supplier            | categories                                                             |
      | SWT0001 | BienenhoniK - Karl Süßkleber | 5.20  | Bienenstock         | Root > Deutsch > Nahrungsmittel > Süß                                  |
      | SWT0002 | Sushi-Reis                   | 12    | KendalJP Inc.       | Root > Deutsch > Nahrungsmittel > Getreide                             |
      | SWT0003 | Sommerhandschuhe "Sansibar"  | 35.50 | Kunstschneerasen AG | Root > Deutsch > Kleidung > Herren ; Root > Deutsch > Kleidung > Damen |

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