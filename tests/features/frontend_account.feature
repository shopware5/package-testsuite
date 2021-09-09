@javascript @account @frontend
Feature: I can use the customer account page

  Background:
    Given the following customer accounts exist:
      | email                             | password | group | country |
      | regular.customer@shopware.de.test | shopware |       | DE      |

    And I am logged in with account "regular.customer@shopware.de.test" with password "shopware"

  Scenario: I can add a new address
    When I am on "/address"
    Then I should see "Adressen"
    Then I should see "Standard-Lieferadresse"
    Then I should see "Standard-Rechnungsadresse"

    When I follow "Neue Adresse hinzufügen"
    Then I should see "Neue Adresse erstellen"
    When I select "mr" from "address[salutation]"
    And I fill in the following:
      | address[firstname] | Bruce |
      | address[lastname]  | Wayne |
      | address[street]    | South Street 123 |
      | address[zipcode]   | 12345 |
      | address[city]      | Gotham City |
    And I select "2" from "address[country]"
    And I scroll down "100" px
    When I click on "Adresse speichern"
    Then I should see "Die Adresse wurde erfolgreich erstellt"
