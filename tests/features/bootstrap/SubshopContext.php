<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Backend\ShopSettings;

class SubshopContext extends SubContext
{
    /** @var \PDO $dbConnection */
    private static $dbConnection;

    /**
     * @Then I should be able to access the subshop via using :url
     */
    public function iShouldBeAbleToAccessTheSubshopViaUsing($url)
    {
        $this->getSession()->visit($url);
    }

    /**
     * @When I click the :label menu element
     */
    public function iClickTheMenuElement($label)
    {
        /** @var ShopSettings $page */
        $page = $this->getPage('ShopSettings');
        $page->clickOnMenuElement($label);
    }

    /**
     * @When I click the :label settings element
     */
    public function iClickTheSettingsElement($label)
    {
        /** @var ShopSettings $page */
        $page = $this->getPage('ShopSettings');
        $page->clickOnSettingsMenuElement($label);
    }

    /**
     * @Given I fill in and submit the :formname configuration form:
     */
    public function iFillTheConfigurationForm($formname, TableNode $table)
    {
        /** @var ShopSettings $page */
        $page = $this->getPage('ShopSettings');

        $data = $table->getHash();
        $page->fillConfigurationForm($data);
    }

    /**
     * @AfterFeature @subshop
     */
    public static function cleanupFeature(AfterFeatureScope $scope)
    {
        self::getDbConnection()->exec('DELETE FROM s_core_shops WHERE id=3;');
        self::getDbConnection()->exec('DELETE FROM s_core_rewrite_urls WHERE subshopID = 3;');
        self::getDbConnection()->exec('DELETE FROM s_core_config_values WHERE shop_id = 3;');
        self::getDbConnection()->exec('DELETE FROM s_categories WHERE id IN (5, 6);');
    }


    /**
     * Establishes database connection
     */
    public static function getDbConnection()
    {
        if (self::$dbConnection === null) {
            self::$dbConnection = new \PDO('mysql:dbname=shopware;host=mysql', 'shopware', 'shopware');
        }
        return self::$dbConnection;
    }
}
