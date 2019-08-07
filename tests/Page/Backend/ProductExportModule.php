<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\GridView\GridViewRow;

class ProductExportModule extends BackendModule
{
    /** @var string */
    protected $path = '/backend/?app=ProductFeed';

    /** @var string */
    protected $moduleWindowTitle = 'Produktexporte';

    /** @var string */
    protected $editorWindowTitle = 'Feed - Konfiguration';

    /**
     * {@inheritdoc}
     */
    public function verify(array $urlParameters)
    {
        return null !== $this->getModuleWindow();
    }

    /**
     * Fills in the product export configuration form
     *
     * @param array $formData
     */
    public function fillConfigurationForm($formData)
    {
        $editor = $this->getEditorWindow(false);
        $this->fillExtJsForm($editor, $formData);
    }

    /**
     * Enters the template on which the product export is based of
     *
     * @param string $template The template itself
     * @throws \Exception
     */
    public function enterTemplate($template)
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
     * @param $exportTitle
     * @throws \Exception
     */
    public function openExport($exportTitle)
    {
        $window = $this->getModuleWindow();

        $exportRow = $window->getGridView()->getRowByContent($exportTitle);

        $exportUrl = $this->getExportUrl($exportRow);
        $this->getSession()->visit($exportUrl);
    }

    /**
     * @param string $supplierName
     */
    public function blockSupplier($supplierName)
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

    /**
     * @param string $minPrice
     */
    public function addMinimumPriceFilter($minPrice)
    {
        $this->getEditorWindow()->getInput('Preis grösser:')->setValue((int)$minPrice);
    }

    /**
     * Click the edit icon on the given export
     *
     * @param string $exportName
     */
    public function clickEditIconForExport($exportName)
    {
        $exportRow = $this->getModuleWindow()->getGridView()->getRowByContent($exportName);
        $exportRow->clickActionIcon('sprite-pencil');
    }

    /**
     * @param string $expected
     * @throws \Exception
     */
    public function checkExportResult($expected)
    {
        $actual = $this->getText();

        if ($this->normalizeText($expected) !== $this->normalizeText($actual)) {
            throw new \Exception('Product stream not as expected.. Expected: ' . $expected . ' but got ' . $actual);
        }
    }

    /**
     * Normalize a given string by removing tabs, spaces and newlines from, allowing better comparison
     *
     * @param string $text
     * @return string
     */
    private function normalizeText($text)
    {
        return str_replace([' ', '\t', '\n'], '', $text);
    }

    /**
     * @param GridViewRow $exportRow
     * @return string
     */
    private function getExportUrl(GridViewRow $exportRow)
    {
        $fileLinkXpath = BackendXpathBuilder::create()->descendant('a', [], 1)->getXpath();
        $fileLink = $exportRow->find('xpath', $fileLinkXpath)->getAttribute('href');

        $baseUrlIndex = strpos($this->getDriver()->getCurrentUrl(), '/backend/');
        $baseUrl = substr($this->getDriver()->getCurrentUrl(), 0, $baseUrlIndex);

        return $baseUrl . $fileLink;
    }
}
