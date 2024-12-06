<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\GridView\GridViewRow;

class ProductExportModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=ProductFeed';

    protected string $moduleWindowTitle = 'Produktexporte';

    protected string $editorWindowTitle = 'Feed - Konfiguration';

    /**
     * {@inheritdoc}
     */
    public function verify(array $urlParameters): bool
    {
        try {
            $this->getModuleWindow();
        } catch (ElementNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Fills in the product export configuration form
     */
    public function fillConfigurationForm(array $formData): void
    {
        $editor = $this->getEditorWindow(false);
        $this->fillExtJsForm($editor, $formData);
    }

    /**
     * Enters the template on which the product export is based of
     *
     * @param string $template The template itself
     *
     * @throws Exception
     */
    public function enterTemplate(string $template): void
    {
        $editor = $this->getEditorWindow(false);

        $templateAreaXpath = BackendXpathBuilder::create()->child('pre', ['~class' => 'ace_editor'])->getXpath();
        $this->waitForXpathElementPresent($templateAreaXpath);

        $templateArea = $editor->find('xpath', $templateAreaXpath);

        $textareaXpath = BackendXpathBuilder::create()->descendant('textarea', [], 1)->getXpath();
        $textarea = $templateArea->find('xpath', $textareaXpath);

        $templateArea->click();
        $textarea->setValue($template);
    }

    /**
     * Open a product export
     *
     * @throws Exception
     */
    public function openExport(string $exportTitle): void
    {
        $exportRow = $this->getModuleWindow()->getGridView()->getRowByContent($exportTitle);

        $exportUrl = $this->getExportUrl($exportRow);
        $this->getSession()->visit($exportUrl);
    }

    public function blockSupplier(string $supplierName): void
    {
        $supplierXpath = BackendXpathBuilder::create()
            ->child('span', ['@text' => 'Verfügbare Hersteller'])
            ->ancestor('div', ['~class' => 'x-panel'])
            ->descendant('div', ['~class' => 'x-grid-cell-inner'])
            ->contains($supplierName)
            ->getXpath();

        $supplierRow = $this->waitForSelectorPresent('xpath', $supplierXpath);
        $supplierRow->click();

        // Click add-to-blocked-suppliers-button
        $buttonXpath = BackendXpathBuilder::create()
            ->child('span', ['~class' => 'x-form-itemselector-add'])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $buttonXpath)->click();
    }

    public function addMinimumPriceFilter(string $minPrice): void
    {
        $this->getEditorWindow()->getInput('Preis grösser:')->setValue($minPrice);
    }

    /**
     * Click the edit icon on the given export
     */
    public function clickEditIconForExport(string $exportName): void
    {
        $exportRow = $this->getModuleWindow()->getGridView()->getRowByContent($exportName);
        $exportRow->clickActionIcon('sprite-pencil');
    }

    /**
     * @throws Exception
     */
    public function checkExportResult(string $expected): void
    {
        $actual = $this->getText();

        if ($this->normalizeText($expected) !== $this->normalizeText($actual)) {
            throw new Exception('Product stream not as expected.. Expected: ' . $expected . ' but got ' . $actual);
        }
    }

    /**
     * Normalize a given string by removing tabs, spaces and newlines from, allowing better comparison
     */
    private function normalizeText(string $text): string
    {
        return str_replace([' ', '\t', '\n'], '', $text);
    }

    private function getExportUrl(GridViewRow $exportRow): string
    {
        $fileLinkXpath = BackendXpathBuilder::create()->descendant('a', [], 1)->getXpath();
        $fileLink = $exportRow->find('xpath', $fileLinkXpath)->getAttribute('href');

        $baseUrlIndex = strpos($this->getDriver()->getCurrentUrl(), '/backend/');
        $baseUrl = substr($this->getDriver()->getCurrentUrl(), 0, (int) $baseUrlIndex);

        return $baseUrl . $fileLink;
    }
}
