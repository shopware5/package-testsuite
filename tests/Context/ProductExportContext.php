<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\ProductExportModule;

class ProductExportContext extends SubContext
{
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
     * @Then I should be able to enter my basic template :template
     * @param string $template
     */
    public function iShouldBeAbleToEnterMyBasicTemplate($template)
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');
        $page->enterTemplate($template);
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
}
