@javascript @backend @revision
Feature: The revision and revision build number match the installation

  Scenario: The revision and revision build number match the installation
    Given I am logged into the backend
    Then I should see "Marketing" eventually
    Then I hover over backend menu item question mark
    Then I click on "Über Shopware"
    Then I should see a correct build number
    And I should see a correct version number