@javascript @backend
Feature: I can manage my orders with the backend order module

  @isolated
  Scenario:
    Given the following orders exist:
      | customer.email              | position.name | position.quantity | position.price |
      | order.test@shopware.de.test | Testartikel   | 2                 | 10.99          |

    When I am on the page "BackendLogin"
    And I log in with user "demo" and password "demo"
    And I hover backend menu item "Kunden"
    And I click on backend menu item that contains "Bestellungen"
    Then I should see "order.test@shopware.de.test" eventually

    When I open the order from email "order.test@shopware.de.test"
    Then I should see "Bestellungs-Details" eventually

    ## Order status change
    When I change the order status to "Komplett abgeschlossen"
    And I change the payment status to "Komplett bezahlt"
    And I click the "Speichern" Button
    And I click on the "Status History" tab
    And I reload the status history
    Then I should see "Komplett abgeschlossen" eventually
    And I should see "Komplett bezahlt" eventually

    ## Document creation
    When I click on the "Dokumente" tab
    And I click the "Dokument erstellen" Button
    Then I should eventually see a generated invoice

    When I click the email icon on the last generated document
    Then I should see "E-Mail an den Kunden senden" eventually

  Scenario: I can filter and sort orders in the backend
    Given the following orders exist:
      | customer.email              | position.name | position.quantity | position.price | shipping.country |
      | order.test@shopware.de.test | Testartikel   | 1                 | 10.99          | DE               |
      | demo.test@shopware.de.test  | Testartikel   | 3                 | 15.99          | GB               |

    Given I am on the page "BackendLogin"
    And I log in with user "demo" and password "demo"
    And I am on the page "OrderModule"

    When I filter the backend order list for shipping country "Deutschland"
    Then I should see exactly 1 order in the order list

    When I click the "Zurücksetzen" Button
    And I filter the backend order list for shipping country "Großbritannien"
    Then I should see exactly 1 order in the order list

    When I click the "Zurücksetzen" Button
    And I sort the backend order list by order value ascendingly
    Then I should see the order from "order.test@shopware.de.test" at the top of the order list
