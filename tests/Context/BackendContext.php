<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Context\Exception\TooManyElementsFoundException;
use Shopware\Page\Backend\Backend;
use Shopware\Page\Backend\ShippingModule;

class BackendContext extends SubContext
{
    /**
     * @Given I am logged into the backend
     * @When  I log in with user :user and password :password
     */
    public function iLogInWithUserAndPassword(string $user = 'demo', string $password = 'demo'): void
    {
        $page = $this->getValidPage('Backend', Backend::class);
        $page->login($user, $password);
    }

    /**
     * @When I hover backend menu item :item
     */
    public function iHoverBackendMenuItem(string $itemName): void
    {
        $xpath = "//span[text()='$itemName']";
        $this->waitForSelectorPresent('xpath', $xpath);
        $this->getSession()->getDriver()->mouseOver($xpath);
    }

    /**
     * @When I click on backend menu item :item
     */
    public function iClickOnBackendMenuItem(string $itemName): void
    {
        $this->getSession()->getDriver()->click("//span[text()='$itemName']/ancestor::a[1]");
    }

    /**
     * @When I click on backend menu item that contains :text
     */
    public function iClickOnBackendMenuItemThatContains(string $text): void
    {
        $this->getSession()->getDriver()->click("//span[contains(., '$text')]/ancestor::a[1]");
    }

    /**
     * @Given the following shipping options exist:
     */
    public function theFollowingShippingOptionsExist(TableNode $table): void
    {
        $page = $this->getValidPage('Backend', Backend::class);
        $page->login();

        $page = $this->getValidPage('ShippingModule', ShippingModule::class);

        foreach ($table->getHash() as $shipping) {
            $page->createShippingMethodIfNotExists($shipping);
        }
    }

    /**
     * @Given the shipping method :method has the following shipping costs:
     */
    public function theShippingMethodHasTheFollowingShippingCosts(string $method, TableNode $table): void
    {
        $page = $this->getValidPage('ShippingModule', ShippingModule::class);
        $page->setShippingCosts($method, $table->getHash());
    }

    /**
     * @When I click the :label button
     */
    public function clickButtonByLabel(string $label): void
    {
        $page = $this->getValidPage('Backend', Backend::class);
        $buttonXpath = BackendXpathBuilder::getButtonXpathByLabel($label);
        $this->waitForSelectorPresent('xpath', $buttonXpath);
        $buttons = $page->findAll('xpath', $buttonXpath);
        $buttons = array_filter($buttons, static function (NodeElement $button) {
            return $button->isVisible() && !$button->hasAttribute('disabled');
        });

        if (empty($buttons)) {
            throw new ElementNotFoundException($this->getDriver(), 'button', 'xpath', $buttonXpath);
        }

        if (\count($buttons) !== 1) {
            throw new TooManyElementsFoundException($this->getDriver(), 'button', 'xpath', $buttonXpath);
        }

        $button = array_shift($buttons);
        $button->click();
    }

    /**
     * @When I click on the :tabName tab
     */
    public function iClickOnTheTab(string $tabName): void
    {
        $page = $this->getValidPage('Backend', Backend::class);
        $page->clickOnTabWithName($tabName);
    }
}
