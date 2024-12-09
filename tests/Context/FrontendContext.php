<?php

declare(strict_types=1);

namespace Shopware\Context;

use Exception;
use PHPUnit\Framework\Assert;
use Shopware\Component\XpathBuilder\BaseXpathBuilder;
use Shopware\Page\Frontend\Account;
use Shopware\Page\Frontend\CheckoutCart;
use Shopware\Page\Frontend\Index;

class FrontendContext extends SubContext
{
    /**
     * @Given /^I am logged in with account "([^"]*)"(?: with password "([^"]*)")?$/
     */
    public function iAmLoggedInWithAccount(string $email, string $password = ''): void
    {
        $accountPage = $this->getValidPage(Account::class);
        $accountPage->open();

        // We are logged in
        if ($this->waitIfThereIsText($email)) {
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
     *
     * @throws Exception
     */
    public function theCartShouldContainArticlesWithAValueOf(string $quantity, string $amount): void
    {
        $page = $this->getValidPage(CheckoutCart::class);
        $page->open();

        $page->checkPositionCountAndCartSum($quantity, $amount);
    }

    /**
     * @When I navigate to category tree :tree
     */
    public function iNavigateToCategoryTree(string $tree): void
    {
        $treeArray = explode('>', $tree);
        $treeArray = array_map('trim', $treeArray);
        $mainCategory = array_shift($treeArray);

        $index = $this->getValidPage(Index::class);
        $index->open();
        $this->waitForText('AGB');
        $index->getMainNavElement($mainCategory)->click();
        foreach ($treeArray as $subCategory) {
            $index->getSubNavElement($subCategory)->click();
        }
    }

    /**
     * @Then I should be able to see the product :name with price :testPrice
     */
    public function iShouldBeAbleToSeeTheProductWithPrice(string $name, string $testPrice): void
    {
        $product = $this->getValidPage(Index::class)->getProductListingBoxElement($name);

        $price = $product->find('xpath', (new BaseXpathBuilder())->descendant('span', ['~class' => 'price--default'])->getXpath());

        $priceText = $price->getText();
        $priceText = explode(' ', $priceText)[0];

        if (!is_numeric($priceText)) {
            $priceText = str_replace(['.', ','], ['', '.'], $priceText);
        }

        Assert::assertEquals((float) $priceText, (float) $testPrice);
    }

    /**
     * @Given I click on :text
     *
     * @throws Exception
     */
    public function iClickOn(string $text): void
    {
        // Trt to find a button with the given text
        $button = $this->getSession()->getPage()->findButton($text);
        if ($button) {
            $button->click();

            return;
        }

        // If there is none, try to find a link
        $link = $this->getSession()->getPage()->findLink($text);
        if ($link) {
            $link->click();

            return;
        }

        throw new Exception('Could not find element by content ' . $text);
    }
}
