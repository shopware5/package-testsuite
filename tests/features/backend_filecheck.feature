@javascript @filecheck
Feature: I can check the files which belong to my shop installation

  Background:
    Given the filecheck requirements are not met

  Scenario: I can check the original state of my folders and files
    Given I am logged into the backend
    Then I should see "Einstellungen" eventually
    When I hover backend menu item "Einstellungen"
    And I click the "Systeminfo" menu element
    Then I should see "Shopware-Verzeichnisse" eventually

    When I click on the "Shopware-Verzeichnisse" tab
    Then a "folder" requirement should have a "cross" as status
    When I correct the "folder" requirement
    And I reload the page
    Then I should see "Einstellungen" eventually
    When I hover backend menu item "Einstellungen"
    And I click the "Systeminfo" menu element
    Then I should see "Shopware-Verzeichnisse" eventually

    When I click on the "Shopware-Verzeichnisse" tab
    Then all "folder" requirements should have a "tick" as status

    When I click on the "Shopware-Dateien" tab
    Then a "file" requirement should have a "cross" as status

    When I correct the "file" requirement
    And I reload the page
    Then I should see "Einstellungen" eventually
    When I hover backend menu item "Einstellungen"
    And I click the "Systeminfo" menu element
    Then I should see "Shopware-Verzeichnisse" eventually

    When I click on the "Shopware-Dateien" tab
    Then all "file" requirements should have a "tick" as status