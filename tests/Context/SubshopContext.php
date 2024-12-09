<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\SettingsModule;

class SubshopContext extends SubContext
{
    /**
     * @Then I should be able to access the subshop via using :url
     * @Then I should be able to access the shop via using :url
     */
    public function iShouldBeAbleToAccessTheSubshopViaUsing(string $url): void
    {
        $this->getSession()->visit($url);
    }

    /**
     * @When I click the :label menu element
     */
    public function iClickTheMenuElement(string $label): void
    {
        $page = $this->getValidPage(SettingsModule::class);
        $page->clickOnMenuElement($label);
    }

    /**
     * @When I click the :label settings element
     */
    public function iClickTheSettingsElement(string $label): void
    {
        $page = $this->getValidPage(SettingsModule::class);
        $page->clickOnSettingsMenuElement($label);
    }

    /**
     * @Given I fill in and submit the :formname configuration form:
     */
    public function iFillTheConfigurationForm(TableNode $table): void
    {
        $page = $this->getValidPage(SettingsModule::class);

        $data = $table->getHash();
        $page->fillShopConfigurationForm($data);
    }
}
