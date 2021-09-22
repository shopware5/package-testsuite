<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Shopware\Page\Backend\ProductExportModule;

class ProductExportContext extends PageObjectContext
{
    /**
     * @When I fill in the product export configuration:
     *
     * @throws \Exception
     */
    public function iFillInTheProductExportGeneralConfiguration(TableNode $table)
    {
        $this->getModulePage()->fillConfigurationForm($table->getHash());
    }

    /**
     * @Then I enter the template
     */
    public function iEnterTheTemplate(PyStringNode $template)
    {
        $this->getModulePage()->enterTemplate($template);
    }

    /**
     * @Given I open the :title export file
     *
     * @param string $title
     *
     * @throws \Exception
     */
    public function iOpenTheCreatedExportFile($title)
    {
        $this->getModulePage()->openExport($title);
    }

    /**
     * @Then it should contain the following product data:
     */
    public function itShouldContainTheFollowingProductData(PyStringNode $expected)
    {
        $this->getModulePage()->checkExportResult($expected);
    }

    /**
     * @Given I block products from supplier :supplierName
     *
     * @param string $supplierName
     */
    public function iBlockProductsFromSupplier($supplierName)
    {
        $this->getModulePage()->blockSupplier($supplierName);
    }

    /**
     * @Given I define a minimum price filter with a value of :minPrice
     *
     * @param string $minPrice
     */
    public function iDefineAMinimumPriceFilterWithAValueOf($minPrice)
    {
        $this->getModulePage()->addMinimumPriceFilter($minPrice);
    }

    /**
     * @Given I click the edit icon on the export :exportName
     *
     * @param string $exportName
     */
    public function iClickTheEditIconOnTheExport($exportName)
    {
        $this->getModulePage()->clickEditIconForExport($exportName);
    }

    /**
     * @return ProductExportModule|null
     */
    private function getModulePage()
    {
        /** @var ProductExportModule $page */
        $page = $this->getPage('ProductExportModule');

        return $page;
    }
}
