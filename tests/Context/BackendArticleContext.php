<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Backend\ExistingArticleModule;
use Shopware\Page\Backend\NewArticleModule;
use Shopware\Page\Backend\BackendModule;

class BackendArticleContext extends SubContext
{
    /**
     * @return BackendModule
     * @throws \Exception
     */
    private function getBackendModulePage()
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        if ($page === null) {
            throw new \RuntimeException('Page is not defined.');
        }
        return $page;
    }

    /**
     * @return ExistingArticleModule
     * @throws \Exception
     */
    private function getExistingArticleModulePage()
    {
        /** @var ExistingArticleModule $page */
        $page = $this->getPage('ExistingArticleModule');
        if ($page === null) {
            throw new \RuntimeException('Page is not defined.');
        }
        return $page;
    }


    /**
     * @return NewArticleModule
     * @throws \Exception
     */
    private function getNewArticleModulePage()
    {
        /** @var NewArticleModule $page */
        $page = $this->getPage('NewArticleModule');
        if ($page === null) {
            throw new \RuntimeException('Page is not defined.');
        }
        return $page;
    }

    /**
     * @Given I set :price as the article price
     *
     * @param string $price
     * @throws \Exception
     */
    public function iSetAsTheArticlePriceForTheCustomerGroup($price)
    {
        $this->getNewArticleModulePage()->setArticlePriceData($price, 'Preis', 'price');
    }

    /**
     * @Given I choose :text as article description
     *
     * @param string $text
     * @throws \Exception
     */
    public function iChooseAsArticleDescription($text)
    {
        $this->getNewArticleModulePage()->setDescription($text);
    }

    /**
     * @Then I am able to save my article
     *
     * @throws \Exception
     */
    public function iAmAbleToSaveMyArticle()
    {
        $this->getNewArticleModulePage()->saveArticle();
    }

    /**
     * @When I click to add the category with name :name to the article
     *
     * @param string $name
     * @throws \Exception
     */
    public function iClickTheIconToAdd($name)
    {
        $this->getNewArticleModulePage()->addCategory($name);
    }

    /**
     * @Then I should find the category with name :title in :area
     *
     * @param string $title
     * @param string $area
     * @throws \Exception
     */
    public function iShouldFindInTheArea($title, $area)
    {
        $this->getNewArticleModulePage()->checkAddedCategory($title, $area);
    }

    /**
     * @When I fill in the basic configuration:
     *
     * @param TableNode $table
     * @throws \Exception
     */
    public function iFillInTheBasicConfiguration(TableNode $table)
    {
        $data = $table->getHash();
        $this->getNewArticleModulePage()->setBasicData($data);
    }

    /**
     * @When I expand the :label element
     *
     * @param string $label
     * @throws \Exception
     */
    public function iExpandTheCategoryElement($label)
    {
        $this->getBackendModulePage()->expandCategoryCollapsible($label);
    }

    /**
     * @Then I check if my article data is displayed:
     *
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
     *
     * @param string $articlename
     * @throws \Exception
     */
    public function iChangeTheArticleNameTo($articlename)
    {
        $this->getExistingArticleModulePage()->changeArticleName($articlename);
    }

    /**
     * @When I click the edit icon of the entry :name
     *
     * @param string $name
     * @throws \Exception
     */
    public function iClickTheEditIconOfTheEntry($name)
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-pencil');
    }

    /**
     * @When I click the delete icon of the entry :name
     *
     * @param string $name
     * @throws \Exception
     */
    public function iClickTheDeleteIconOfTheEntry($name)
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-minus-circle-frame');
    }

    /**
     * @Given I confirm to delete the entry
     *
     * @throws \Exception
     */
    public function iConfirmToDeleteTheEntry()
    {
        $this->getBackendModulePage()->answerMessageBox('Ja');
    }

    /**
     * @Given the :title tab should be active
     *
     * @param string $title
     * @throws \Exception
     */
    public function theTabShouldBeActive($title)
    {
        if ($this->getBackendModulePage()->checkIfTabIsActive($title) !== true) {
            throw new \RuntimeException('Variant was not set active.');
        }
    }

    /**
     * @When I create the :title group via :label
     *
     * @param string $groupname
     * @param string $label
     * @throws \Exception
     */
    public function iCreateTheGroup($groupname, $label)
    {
        $this->getExistingArticleModulePage()->createVariantGroup($groupname, $label);
    }

    /**
     * @Given the group :title should be listed in the area :area
     * @Given the option :title should be listed in the area :area
     *
     * @param string $title
     * @param string $area
     * @throws \Exception
     */
    public function theGroupShouldBeListedAsAnActiveGroup($title, $area)
    {
        $this->getExistingArticleModulePage()->checkIfMatchesTheRightGroup($title, $area);
    }

    /**
     * @When I click :group to create the options of it
     *
     * @param string $groupname
     * @throws \Exception
     */
    public function iClickToCreateTheOptionsOfIt($groupname)
    {
        $this->getExistingArticleModulePage()->clickToEditGroup($groupname);
    }

    /**
     * @Then I create the following options options:
     *
     * @param TableNode $table
     * @throws \Exception
     */
    public function iCreateTheFollowingOptionsOptions(TableNode $table)
    {
        $this->getExistingArticleModulePage()->createOptionsForGroup($table->getHash(), 'Optionen erstellen:');
    }

    /**
     * @When I limit the price :price for an amount up to :max
     * @When I set the price :price for any number from here
     *
     * @param string $price
     * @param int $maxAmount
     *
     * @throws \Exception
     */
    public function iLimitThePriceForAnAmountOfTo($price, $maxAmount = 0)
    {
        $this->getExistingArticleModulePage()->setArticlePriceData($price, 'Preis', 'price');

        if ($maxAmount !== 0) {
            $this->getExistingArticleModulePage()->setArticlePriceData($maxAmount, 'Bis', 'to');
        }
    }

    /**
     * @Then I should see :price as to-price
     *
     * @param string $price
     */
    public function iShouldSeeAsToPrice($price)
    {
        $this->waitForText($price);
    }

    /**
     * @Given I should see :amount as from-price to any number
     *
     * @param string $amount
     */
    public function iShouldSeeAsFromPriceToAnyNumber($amount)
    {
        $this->waitForText($amount);
        $this->waitForText('Beliebig');
    }

    /**
     * @When I fill in the property configuration:
     *
     * @param TableNode $table
     * @throws \Exception
     */
    public function iFillInThePropertyConfiguration(TableNode $table)
    {
        $data = $table->getHash();
        $this->getExistingArticleModulePage()->selectProperty($data);
    }

    /**
     * @Then I should see :group as corresponding value to :value
     *
     * @param string $group
     * @param string $value
     * @throws \Exception
     */
    public function iShouldSeeAsCorrespondingValueTo(string $group, string $value): void
    {
        $this->getExistingArticleModulePage()->checkCorrespondingPropertyValues($group, $value);
    }

    /**
     * @When /^I open inline editing of variant "([^"]*)" and add "([^"]*)"$/
     */
    public function iOpenInlineEditingOfVariantAndAdd(string $orderNumber, string $additionalText): void
    {
        $this->getExistingArticleModulePage()->doInlineEditingOfVariant($orderNumber, $additionalText);
    }

    /**
     * @When /^I open variant detail page of variant "([^"]*)"$/
     */
    public function iOpenVariantDetailPageOfVariant(string $orderNumber)
    {
        $this->getExistingArticleModulePage()->openVariantDetailPage($orderNumber);
    }
}
