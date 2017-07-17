<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\SettingsModule;

class SubshopContext extends SubContext
{
    /**
     * @Given I am in subshop with URL :url
     * @param $url
     */
    public function iAmInSubshopWithURL($url)
    {
        if (substr($url, 0, 4) === "http") {
            $this->setMinkParameters([
                'base_url' => $url,
            ]);
            return;
        }
        $baseUrl = $this->getMinkParameter('base_url');
        $this->setMinkParameters([
            'base_url' => rtrim($baseUrl, "/") . "/" . ltrim($url, "/"),
        ]);
    }

    /**
     * @Then I should be able to access the subshop via using :url
     * @param string $url
     */
    public function iShouldBeAbleToAccessTheSubshopViaUsing($url)
    {
        $this->getSession()->visit($url);
    }

    /**
     * @When I click the :label menu element
     * @param string $label
     */
    public function iClickTheMenuElement($label)
    {
        /** @var SettingsModule $page */
        $page = $this->getPage('SettingsModule');
        $page->clickOnMenuElement($label);
    }

    /**
     * @When I click the :label settings element
     * @param string $label
     */
    public function iClickTheSettingsElement($label)
    {
        /** @var SettingsModule $page */
        $page = $this->getPage('SettingsModule');
        $page->clickOnSettingsMenuElement($label);
    }

    /**
     * @Given I fill in and submit the :formname configuration form:
     * @param $formname
     * @param TableNode $table
     */
    public function iFillTheConfigurationForm($formname, TableNode $table)
    {
        /** @var SettingsModule $page */
        $page = $this->getPage('SettingsModule');

        $data = $table->getHash();
        $page->fillShopConfigurationForm($data);
    }
}
