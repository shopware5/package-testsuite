<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Backend\ArticleModule;
use Shopware\Page\Backend\BackendModule;

class ArticleContext extends SubContext
{
    /**
     * @Given I click the :label extended menu element
     * @param string $label
     */
    public function iClickTheExtendedMenuElement($label)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->clickOnExtendedElement($label);
    }

    /**
     * @Given I set :price as the article price
     * @param string $price
     */
    public function iSetAsTheArticlePriceForTheCustomerGroup($price)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->setPriceForCustomerGroup($price);
    }

    /**
     * @Given I choose :text as article description
     * @param string $text
     */
    public function iChooseAsArticleDescription($text)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->setDescription($text);
    }

    /**
     * @Then I am be able to save my article
     */
    public function iAmBeAbleToSaveMyArticle()
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        $buttonXpath = BackendXpathBuilder::getButtonXpathByLabel('Artikel speichern');
        $button = $page->find('xpath', $buttonXpath);
        $button->click();
    }


    /**
     * @When I click the :icon icon to add :name
     * @param string $icon
     * @param string $name
     */
    public function iClickTheIconToAdd($icon, $name)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->addCategory($icon, $name);
    }

    /**
     * @Then I should find :title in the area :area
     * @param string $title
     * @param string $area
     */
    public function iShouldFindInTheArea($title, $area)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->checkAddedCategory($title, $area);
    }

    /**
     * @When I set :shoptitle as the shop for the preview
     * @When I fill in the preview configuration:
     * @param string $label
     */
    public function iSetAsShopForThePreview($label)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->chooseShopForPreview($label);
    }

    /**
     * @When I fill in the basic configuration:
     * @param TableNode $table
     */
    public function iFillInTheBasicConfiguration(TableNode $table)
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');

        $data = $table->getHash();
        $page->setBasicData($data);
    }


    /**
     * @When I expand the :label element
     * @param string $label
     */
    public function iExpandTheElement($label)
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        $page->expandCollapsible($label);
    }

    /**
     * @Given I start the preview
     */
    public function iStartThePreview()
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        $page->startPreview();
    }


    /**
     * @Then I check if my article data is displayed:
     * @param TableNode $table
     */
    public function iCheckIfMyArticleDataIsDisplayed1(TableNode $table)
    {
        $data = $table->getHash();

        foreach ($data as $product) {
            $this->waitForText($product['info']);
        }
    }
}