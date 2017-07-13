<?php

namespace Shopware\Tests\Mink;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\BaseXpathBuilder;
use Shopware\Tests\Mink\Element\HeaderCart;
use Shopware\Tests\Mink\Page\Frontend\Account;
use Shopware\Tests\Mink\Page\Frontend\CheckoutCart;
use Shopware\Tests\Mink\Page\Frontend\Index;

class FrontendContext extends SubContext
{

    /**
     * @Given /^I am logged in with account "([^"]*)"(?: with password "([^"]*)")?$/
     */
    public function iAmLoggedInWithAccount($email, $password = '')
    {
        /** @var Account $accountPage */
        $accountPage = $this->getPage('Account');
        $accountPage->open();

        // We are logged in
        if($this->waitIfThereIsText($email)) {
            return;
        }

        // We are logged in as the wrong customer
        if ($this->checkIfThereIsText('Abmelden', $this)) {
            $accountPage->clickLink('Abmelden');
        }

        if (empty($password)) {
            $password = $this->slugify($email);
        }

        $accountPage->login($email, $password);

        $this->waitForText('Willkommen');
    }

    /**
     * @Then the cart should contain :quantity articles with a value of :amount
     */
    public function theCartShouldContainArticlesWithAValueOf($quantity, $amount)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $page->open();
        /** @var HeaderCart $headerCart */
        $headerCart = $this->getElement('HeaderCart');
        $headerCart->checkCart($quantity, $amount);
    }

    /**
     * @When I navigate to category tree :tree
     */
    public function iNavigateToCategoryTree($tree)
    {
        $tree = explode('>', $tree);
        $tree = array_map(function ($string) {
            return trim($string);
        }, $tree);
        $mainCategory = array_shift($tree);
        /** @var Index $index */
        $index = $this->getPage('Index');
        $index->open();
        $this->waitForText('AGB');
        $index->getMainNavElement($mainCategory)->click();
        foreach ($tree as $subCategory) {
            $index->getSubNavElement($subCategory)->click();
        }
    }

    /**
     * @Then I should be able to see the product :name with price :testPrice
     */
    public function iShouldBeAbleToSeeTheProductWithPrice($name, $testPrice)
    {
        /** @var Index $page */
        $page = $this->getPage('Index');

        $product = $page->getProductListingBoxElement($name);
        if ($product == null) {
            throw new ElementNotFoundException(sprintf("Product with ordernumber %s not found!", $name));
        }

        $price = $product->find('xpath', (new BaseXpathBuilder())->descendant('span', ['~class' => 'price--default'])->getXpath());

        $priceText = $price->getText();
        $priceText = explode(' ', $priceText)[0];

        if (!is_numeric($priceText)) {
            $priceText = str_replace(',', '.', str_replace('.', '', $priceText));
        }

        \PHPUnit_Framework_Assert::assertEquals(floatval($priceText), floatval($testPrice));
    }
}
