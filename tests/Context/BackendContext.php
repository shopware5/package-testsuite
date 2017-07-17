<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Backend\Backend;
use Shopware\Page\Backend\BackendLogin;
use Shopware\Page\Backend\ShippingModule;

class BackendContext extends SubContext
{
    /**
     * @When I log in with user :user and password :password
     * @param string $user
     * @param string $password
     */
    public function iLogInWithUserAndPassword($user, $password)
    {
        /** @var BackendLogin $page */
        $page = $this->getPage('BackendLogin');
        $page->login($user, $password);
    }

    /**
     * @When I hover backend menu item :item
     * @param string $itemName
     */
    public function iHoverBackendMenuItem($itemName)
    {
        $xpath = "//span[text()='$itemName']";
        $this->waitForSelectorPresent('xpath', $xpath);
        $this->getSession()->getDriver()->mouseOver($xpath);
    }

    /**
     * @When I click on backend menu item :item
     * @param string $itemName
     */
    public function iClickOnBackendMenuItem($itemName)
    {
        $this->getSession()->getDriver()->click("//span[text()='$itemName']/ancestor::a[1]");
    }

    /**
     * @When I click on backend menu item that contains :text
     * @param string $text
     */
    public function iClickOnBackendMenuItemThatContains($text)
    {
        $this->getSession()->getDriver()->click("//span[contains(., '$text')]/ancestor::a[1]");
    }

    /**
     * @Given the following shipping options exist:
     * @param TableNode $table
     */
    public function theFollowingShippingOptionsExist(TableNode $table)
    {
        /** @var BackendLogin $page */
        $page = $this->getPage('BackendLogin');
        $page->login();

        /** @var ShippingModule $page */
        $page = $this->getPage('ShippingModule');

        foreach ($table->getHash() as $shipping) {
            $page->createShippingMethodIfNotExists($shipping);
        }
    }

    /**
     * @Given the shipping method :method has the following shipping costs:
     * @param string $method
     * @param TableNode $table
     */
    public function theShippingMethodHasTheFollowingShippingCosts($method, TableNode $table)
    {
        /** @var ShippingModule $page */
        $page = $this->getPage('ShippingModule');
        $page->setShippingCosts($method, $table->getHash());
    }

    /**
     * @When I click the :label Button
     * @param string $label
     */
    public function clickButtonByLabel($label)
    {
        $page = $this->getPage('Backend');
        $buttonXpath = BackendXpathBuilder::getButtonXpathByLabel($label);
        $button = $page->find('xpath', $buttonXpath);
        $button->click();
    }

    /**
     * @When I click on the :tabName tab
     * @param string $tabName
     */
    public function iClickOnTheTab($tabName)
    {
        /** @var Backend $page */
        $page = $this->getPage('Backend');
        $page->clickOnTabWithName($tabName);
    }
}
