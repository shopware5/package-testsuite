<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\BackendModule;
use Shopware\Page\Backend\ExistingArticleModule;
use Shopware\Page\Backend\NewArticleModule;

class BackendArticleContext extends SubContext
{
    /**
     * @throws \Exception
     */
    private function getBackendModulePage(): BackendModule
    {
        return $this->getValidPage('BackendModule', BackendModule::class);
    }

    /**
     * @throws \Exception
     */
    private function getExistingArticleModulePage(): ExistingArticleModule
    {
        return $this->getValidPage('ExistingArticleModule', ExistingArticleModule::class);
    }

    /**
     * @throws \Exception
     */
    private function getNewArticleModulePage(): NewArticleModule
    {
        return $this->getValidPage('NewArticleModule', NewArticleModule::class);
    }

    /**
     * @Given I set :price as the article price
     *
     * @throws \Exception
     */
    public function iSetAsTheArticlePriceForTheCustomerGroup(string $price): void
    {
        $this->getNewArticleModulePage()->setArticlePriceData($price, 'Preis', 'price');
    }

    /**
     * @Given I choose :text as article description
     *
     * @throws \Exception
     */
    public function iChooseAsArticleDescription(string $text): void
    {
        $this->getNewArticleModulePage()->setDescription($text);
    }

    /**
     * @Then I am able to save my article
     *
     * @throws \Exception
     */
    public function iAmAbleToSaveMyArticle(): void
    {
        $this->getNewArticleModulePage()->saveArticle();
    }

    /**
     * @When I click to add the category with name :name to the article
     *
     * @throws \Exception
     */
    public function iClickTheIconToAdd(string $name): void
    {
        $this->getNewArticleModulePage()->addCategory($name);
    }

    /**
     * @Then I should find the category with name :title in :area
     *
     * @throws \Exception
     */
    public function iShouldFindInTheArea(string $title, string $area): void
    {
        $this->getNewArticleModulePage()->checkAddedCategory($title, $area);
    }

    /**
     * @When I fill in the basic configuration:
     *
     * @throws \Exception
     */
    public function iFillInTheBasicConfiguration(TableNode $table): void
    {
        $data = $table->getHash();
        $this->getNewArticleModulePage()->setBasicData($data);
    }

    /**
     * @When I expand the :label element
     *
     * @throws \Exception
     */
    public function iExpandTheCategoryElement(string $label): void
    {
        $this->getBackendModulePage()->expandCategoryCollapsible($label);
    }

    /**
     * @Then I check if my article data is displayed:
     */
    public function iCheckIfMyArticleDataIsDisplayed(TableNode $table): void
    {
        foreach ($table->getHash() as $product) {
            $this->waitForText($product['info']);
        }
    }

    /**
     * @When I change the article name to :productName
     *
     * @throws \Exception
     */
    public function iChangeTheArticleNameTo(string $productName): void
    {
        $this->getExistingArticleModulePage()->changeArticleName($productName);
    }

    /**
     * @When I click the edit icon of the entry :name
     *
     * @throws \Exception
     */
    public function iClickTheEditIconOfTheEntry(string $name): void
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-pencil');
    }

    /**
     * @When I click the delete icon of the entry :name
     *
     * @throws \Exception
     */
    public function iClickTheDeleteIconOfTheEntry(string $name): void
    {
        $this->getBackendModulePage()->clickEntryIconByName($name, 'sprite-minus-circle-frame');
    }

    /**
     * @Given I confirm to delete the entry
     *
     * @throws \Exception
     */
    public function iConfirmToDeleteTheEntry(): void
    {
        $this->getBackendModulePage()->answerMessageBox('Ja');
    }

    /**
     * @Given the :title tab should be active
     *
     * @throws \Exception
     */
    public function theTabShouldBeActive(string $title): void
    {
        if ($this->getBackendModulePage()->checkIfTabIsActive($title) !== true) {
            throw new \RuntimeException('Variant was not set active.');
        }
    }

    /**
     * @When I create the :title group via :label
     *
     * @throws \Exception
     */
    public function iCreateTheGroup(string $groupName, string $label): void
    {
        $this->getExistingArticleModulePage()->createVariantGroup($groupName, $label);
    }

    /**
     * @Given the group :title should be listed in the area :area
     * @Given the option :title should be listed in the area :area
     *
     * @throws \Exception
     */
    public function theGroupShouldBeListedAsAnActiveGroup(string $title, string $area): void
    {
        $this->getExistingArticleModulePage()->checkIfMatchesTheRightGroup($title, $area);
    }

    /**
     * @When I click :group to create the options of it
     *
     * @throws \Exception
     */
    public function iClickToCreateTheOptionsOfIt(string $groupName): void
    {
        $this->getExistingArticleModulePage()->clickToEditGroup($groupName);
    }

    /**
     * @Then I create the following options options:
     *
     * @throws \Exception
     */
    public function iCreateTheFollowingOptionsOptions(TableNode $table): void
    {
        $this->getExistingArticleModulePage()->createOptionsForGroup($table->getHash(), 'Optionen erstellen:');
    }

    /**
     * @When I limit the price :price for an amount up to :max
     * @When I set the price :price for any number from here
     *
     * @throws \Exception
     */
    public function iLimitThePriceForAnAmountOfTo(string $price, string $maxAmount = '0'): void
    {
        $this->getExistingArticleModulePage()->setArticlePriceData($price, 'Preis', 'price');

        if ($maxAmount !== '0') {
            $this->getExistingArticleModulePage()->setArticlePriceData($maxAmount, 'Bis', 'to');
        }
    }

    /**
     * @Then I should see :price as to-price
     */
    public function iShouldSeeAsToPrice(string $price): void
    {
        $this->waitForText($price);
    }

    /**
     * @Given I should see :amount as from-price to any number
     */
    public function iShouldSeeAsFromPriceToAnyNumber(string $amount): void
    {
        $this->waitForText($amount);
        $this->waitForText('Beliebig');
    }

    /**
     * @When I fill in the property configuration:
     *
     * @throws \Exception
     */
    public function iFillInThePropertyConfiguration(TableNode $table): void
    {
        $data = $table->getHash();
        $this->getExistingArticleModulePage()->selectProperty($data);
    }

    /**
     * @Then I should see :group as corresponding value to :value
     *
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
    public function iOpenVariantDetailPageOfVariant(string $orderNumber): void
    {
        $this->getExistingArticleModulePage()->openVariantDetailPage($orderNumber);
    }
}
