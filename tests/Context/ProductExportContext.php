<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\ProductExportModule;

class ProductExportContext extends SubContext
{
    /**
     * @When I fill in the product export configuration:
     *
     * @throws \Exception
     */
    public function iFillInTheProductExportGeneralConfiguration(TableNode $table): void
    {
        $this->getModulePage()->fillConfigurationForm($table->getHash());
    }

    /**
     * @Then I enter the template
     */
    public function iEnterTheTemplate(PyStringNode $template): void
    {
        $this->getModulePage()->enterTemplate($template->getRaw());
    }

    /**
     * @Given I open the :title export file
     *
     * @throws \Exception
     */
    public function iOpenTheCreatedExportFile(string $title): void
    {
        $this->getModulePage()->openExport($title);
    }

    /**
     * @Then it should contain the following product data:
     */
    public function itShouldContainTheFollowingProductData(PyStringNode $expected): void
    {
        $this->getModulePage()->checkExportResult($expected->getRaw());
    }

    /**
     * @Given I block products from supplier :supplierName
     */
    public function iBlockProductsFromSupplier(string $supplierName): void
    {
        $this->getModulePage()->blockSupplier($supplierName);
    }

    /**
     * @Given I define a minimum price filter with a value of :minPrice
     */
    public function iDefineAMinimumPriceFilterWithAValueOf(string $minPrice): void
    {
        $this->getModulePage()->addMinimumPriceFilter($minPrice);
    }

    /**
     * @Given I click the edit icon on the export :exportName
     */
    public function iClickTheEditIconOnTheExport(string $exportName): void
    {
        $this->getModulePage()->clickEditIconForExport($exportName);
    }

    private function getModulePage(): ProductExportModule
    {
        return $this->getValidPage('ProductExportModule', ProductExportModule::class);
    }
}
