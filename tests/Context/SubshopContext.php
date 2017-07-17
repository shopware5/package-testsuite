<?php

namespace Shopware\Context;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Backend\SettingsModule;

class SubshopContext extends SubContext
{
    /** @var \PDO $dbConnection */
    private static $dbConnection;
    private static $subshopName = 'SwagTestSubshop';
    private static $mainCategoryName = 'Subshop-Kategorie';
    private static $minorCategoryName = 'Subshop-Unterkategorie';

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

    /**
     * @AfterFeature @subshop
     * @param AfterFeatureScope $scope
     */
    public static function cleanupFeature(AfterFeatureScope $scope)
    {
        $shopStmt = self::getDbConnection()->prepare('SELECT id FROM s_core_shops WHERE name=:shopName');
        $shopStmt->execute([':shopName' => self::$subshopName]);
        $subShopId = $shopStmt->fetchColumn();

        $deleteShopStmt = self::getDbConnection()->prepare('DELETE FROM s_core_shops WHERE name=:shopName');
        $deleteShopStmt->execute([':shopName' => self::$subshopName]);

        $deleteRewriteUrlsStmt = self::getDbConnection()->prepare('DELETE FROM s_core_rewrite_urls WHERE subshopID=:subShopId');
        $deleteRewriteUrlsStmt->execute([':subShopId' => $subShopId]);

        $deleteConfigValuesStmt = self::getDbConnection()->prepare('DELETE FROM s_core_config_values WHERE shop_id=:subShopId');
        $deleteConfigValuesStmt->execute([':subShopId' => $subShopId]);

        $deleteCategoriesStmt = self::getDbConnection()->prepare('DELETE FROM s_categories WHERE `description` IN (:categoryMain, :categoryMinor)');
        $deleteCategoriesStmt->execute([
            ':categoryMain' => self::$mainCategoryName,
            ':categoryMinor' => self::$minorCategoryName,
        ]);
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
