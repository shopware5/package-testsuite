<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\ArticleModule;
use Shopware\Page\Backend\BackendModule;

class BackendArticleContext extends SubContext
{
    /**
     * @Given I set :price as the article price
     * @param string $price
     * @throws \Exception
     */
    public function iSetAsTheArticlePriceForTheCustomerGroup($price)
    {
        $this->getArticleModulePage()->setArticlePrice($price);
    }

    /**
     * @Given I choose :text as article description
     * @param string $text
     * @throws \Exception
     */
    public function iChooseAsArticleDescription($text)
    {
        $this->getArticleModulePage()->setDescription($text);
    }

    /**
     * @Then I am able to save my article
     * @throws \Exception
     */
    public function iAmAbleToSaveMyArticle()
    {
        $this->getArticleModulePage()->saveArticle();
    }

    /**
     * @When I click to add the category with name :name to the article
     * @param string $name
     * @throws \Exception
     */
    public function iClickTheIconToAdd($name)
    {
        $this->getArticleModulePage()->addCategory($name);
    }

    /**
     * @Then I should find the category with name :title in :area
     * @param string $title
     * @param string $area
     * @throws \Exception
     */
    public function iShouldFindInTheArea($title, $area)
    {
        $this->getArticleModulePage()->checkAddedCategory($title, $area);
    }

    /**
     * @When I fill in the basic configuration:
     * @param TableNode $table
     * @throws \Exception
     */
    public function iFillInTheBasicConfiguration(TableNode $table)
    {
        $data = $table->getHash();
        $this->getArticleModulePage()->setBasicData($data);
    }

    /**
     * @When I expand the :label element
     * @param string $label
     * @throws \Exception
     */
    public function iExpandTheCategoryElement($label)
    {
        $this->getBackendModulePage()->expandCategoryCollapsible($label);
    }

    /**
     * @Then I check if my article data is displayed:
     * @param TableNode $table
     */
    public function iCheckIfMyArticleDataIsDisplayed(TableNode $table)
    {
        $data = $table->getHash();

        foreach ($data as $product) {
            $this->waitForText($product['info']);
        }
    }

    /**
     * @When I change the article name to :articlename
     * @throws \Exception
     */
    public function iChangeTheArticleNameTo($articlename)
    {
        $this->getArticleModulePage()->changeArticleName($articlename);
    }

    /**
     * @When I click the edit icon of the entry :name
     *
     * @throws \Exception
     */
    public function iClickTheEditIconOfTheEntry($name)
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-pencil');
    }

    /**
     * @When I click the delete icon of the entry :name
     * @throws \Exception
     */
    public function iClickTheDeleteIconOfTheEntry($name)
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-minus-circle-frame');
    }

    /**
     * @Given I confirm to delete the entry
     * @throws \Exception
     */
    public function iConfirmToDeleteTheEntry()
    {
        $this->getBackendModulePage()->answerMessageBox('Ja');
    }

    /**
     * @return ArticleModule|null
     * @throws \Exception
     */
    private function getArticleModulePage()
    {
        /** @var ArticleModule $page */
        $page = $this->getPage('ArticleModule');
        if ($page !== null) {
            return $page;
        }
        throw new \Exception('Page is not defined.');
    }

    /**
     * @return BackendModule|null
     * @throws \Exception
     */
    private function getBackendModulePage()
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        if ($page !== null) {
            return $page;
        }
        throw new \Exception('Page is not defined.');
    }
}
