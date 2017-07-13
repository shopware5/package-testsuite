<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Backend\ProductExportModule;

class ProductExportContext extends SubContext
{
    /** @var \PDO $dbConnection */
    private static $dbConnection;
    private static $exportName = 'Erster Test-Export';
    private static $mainCategoryName = 'ErsteKategorie';
    private static $minorCategoryName = 'Unterkategorie';
    private static $orderNumbers = ['SWT0001', 'SWT0002', 'SWT0003'];
    private static $supplierNames = ['Bienenstock', 'KendalJP Inc.', 'Kunstschneerasen AG'];
    private static $articleImgNames = ['BienenhoniK-Karl-Sueskleber', 'Sushi-Reis', 'Sommerhandschuhe'];

    /**
     * @When I fill in the product export configuration:
     * @param TableNode $table
     */
    public function iFillInTheProductExportGeneralConfiguration(TableNode $table)
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');

        $data = $table->getHash();
        $page->fillConfigurationForm($data);
    }

    /**
     * @When I start the product export
     */
    public function iStartTheProductExport()
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');
        $page->startExport();
    }

    /**
     * @Then I should be able to enter my basic template :smalltemplate
     */
    public function iShouldBeAbleToEnterMyBasicTemplate($smalltemplate)
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');
        $page->enterTemplate($smalltemplate);
    }

    /**
     * @Then it should contain the following product data:
     * @param TableNode $table
     */
    public function itShouldContainTheFollowingProductData(TableNode $table)
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');

        $data = $table->getHash();
        $page->checkExportResult($data);
    }

    /**
     * @Given I open the :title export file
     * @param string $title
     */
    public function iOpenTheCreatedExportFile($title)
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');
        $page->openExport($title);
    }

    /**
     * @AfterFeature @productexport
     * @param AfterFeatureScope $scope
     */
    public static function cleanupFeature(AfterFeatureScope $scope)
    {
        if(!in_array('productexport', $scope->getFeature()->getTags())) {
            return;
        }

        foreach (self::$orderNumbers as $articleId) {
            self::doDbArticleCleanUp($articleId);
        }

        foreach (self::$supplierNames as $supplierName) {
            self::doSupplierCleanUp($supplierName);
        }

        foreach (self::$articleImgNames as $imgName) {
            self::doDbMediaCleanUp($imgName);
        }

        self::doDbCategoryCleanUp();
        self::doDbExportCleanUp();
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


    /**
     * Cleans up the article-related data
     *
     * @param string $orderNumber The id of the article in the database
     */
    private static function doDbArticleCleanUp($orderNumber)
    {
        $articleStmt = self::getDbConnection()->prepare('SELECT id FROM s_articles_details WHERE ordernumber=:ordernumber');
        $articleStmt->execute([':ordernumber' => $orderNumber]);
        $productDbId = $articleStmt->fetchColumn();

        $deletesSArticlesStmt = self::getDbConnection()->prepare('DELETE FROM s_articles WHERE id=:productDbId');
        $deletesSArticlesStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesAttributesStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_attributes WHERE articleID=:productDbId');
        $deletesSArticlesAttributesStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesCategoriesStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_categories WHERE articleID=:productDbId');
        $deletesSArticlesCategoriesStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesCategoriesRoStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_categories_ro WHERE articleID=:productDbId');
        $deletesSArticlesCategoriesRoStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesDetailsStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_details WHERE articleID=:productDbId');
        $deletesSArticlesDetailsStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesImgStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_img WHERE articleID=:productDbId');
        $deletesSArticlesImgStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesPricesStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_prices WHERE articleID=:productDbId');
        $deletesSArticlesPricesStmt->execute([':productDbId' => $productDbId]);

        $deletesSArticlesTopsellerRoStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_top_seller_ro WHERE articleID=:productDbId');
        $deletesSArticlesTopsellerRoStmt->execute([':productDbId' => $productDbId]);
    }

    /**
     * Cleans up the category-related data
     */
    private static function doDbCategoryCleanUp()
    {
        $deleteCategoriesStmt = self::getDbConnection()->prepare('DELETE FROM s_categories WHERE `description` IN (:categoryMain, :categoryMinor)');
        $deleteCategoriesStmt->execute([
            ':categoryMain' => self::$mainCategoryName,
            ':categoryMinor' => self::$minorCategoryName
        ]);
    }

    /**
     * Cleans up the supplier-related data
     *
     * @param string $supplierName NAme of the supplier
     */
    private static function doSupplierCleanUp($supplierName)
    {
        $supplierStmt = self::getDbConnection()->prepare('SELECT id FROM s_articles_supplier WHERE name=:suppliername');
        $supplierStmt->execute([':suppliername' => $supplierName]);
        $supplierDbId = $supplierStmt->fetchColumn();

        $deletesSArticlesSupplierStmt = self::getDbConnection()->prepare('DELETE FROM s_articles_supplier WHERE id=:supplierId');
        $deletesSArticlesSupplierStmt->execute([':supplierId' => $supplierDbId]);
    }

    /**
     * Cleans up the product export-related data
     */
    private static function doDbExportCleanUp()
    {
        $exportStmt = self::getDbConnection()->prepare('SELECT id FROM s_export WHERE name=:exporttitle');
        $exportStmt->execute([':exporttitle' => self::$exportName]);
        $exportDbId = $exportStmt->fetchColumn();

        $deletesSExportStmt = self::getDbConnection()->prepare('DELETE FROM s_export WHERE id=:exportId');
        $deletesSExportStmt->execute([':exportId' => $exportDbId]);
    }

    /**
     * Cleans up the category-related data
     *
     * @param string $imgName Name of the image
     */
    private static function doDbMediaCleanUp($imgName)
    {
        $mediaStmt = self::getDbConnection()->prepare('SELECT id FROM s_media WHERE name=:medianame');
        $mediaStmt->execute([':medianame' => $imgName]);
        $mediaDbId = $mediaStmt->fetchColumn();

        $deletesSMediaStmt = self::getDbConnection()->prepare('DELETE FROM s_media WHERE id=:mediaId');
        $deletesSMediaStmt->execute([':mediaId' => $mediaDbId]);
    }
}
