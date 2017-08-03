<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class ProductExportModule extends BackendModule
{
    /** @var string */
    protected $moduleWindowTitle = 'Produktexporte';

    /** @var string  */
    protected $editorWindowTitle = 'Feed - Konfiguration';

    /**
     * Fills in and submits the configuration form
     *
     * @param array $formData Defines the form field and their data
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
     */
    public function enterTemplate($template)
    {
        $editor = $this->getEditorWindow(false);

        $templateAreaXpath = BackendXpathBuilder::create()->child('div', ['~class' => 'cm-s-default'])->getXpath();
        $textareaXpath = BackendXpathBuilder::create()->reset()->descendant('textarea', [], 1)->getXpath();
        $this->waitForXpathElementPresent($templateAreaXpath);

        $templateArea = $editor->find('xpath', $templateAreaXpath);
        $this->assertNotNull($templateArea, print_r($templateAreaXpath, true));

        $textarea = $templateArea->find('xpath', $textareaXpath);
        $this->assertNotNull($textarea, print_r($textareaXpath, true));

        $templateArea->click();
        $textarea->setValue($template);
    }

    /**
     * Start a product export
     */
    public function startExport()
    {
        $window = $this->getModuleWindow();

        $exportRowXpath = BackendXpathBuilder::create()
            ->child('div', ['@text' => 'Erster Test-Export'])
            ->ancestor('tr', [], 1)
            ->getXpath();

        $exportRow = $window->find('xpath', $exportRowXpath);

        $startIconXpath = BackendXpathBuilder::create()->child('img', ['~class' => 'sprite-lightning'])->getXpath();
        $startIcon = $exportRow->find('xpath', $startIconXpath);

        $startIcon->click();
    }

    /**
     * Open a product export byt
     *
     * @param $exportTitle
     */
    public function openExport($exportTitle)
    {
        $window = $this->getModuleWindow();

        $exportRowXpath = BackendXpathBuilder::create()->child('div', ['@text' => $exportTitle])->ancestor('tr', [], 1)->getXpath();
        $exportRow = $window->find('xpath', $exportRowXpath);

        $fileLinkXpath = BackendXpathBuilder::create()->descendant('a', [], 1)->getXpath();
        $fileLink = $exportRow->find('xpath', $fileLinkXpath);
        $this->assertNotNull($fileLink, print_r($fileLinkXpath, true));

        $baseUrl = $this->getSession()->getCurrentUrl();
        $this->getSession()->visit(str_replace('/backend/', '', $baseUrl) . $fileLink->getAttribute('href'));
    }

    /**
     * Checks if the export file contains all expected data
     *
     * @param array $data Defines the data which should be found in the file
     */
    public function checkExportResult($data)
    {
        foreach ($data as $product) {
            $this->waitForText($product['number']);
            $this->waitForText($product['name']);
            $this->waitForText($product['price']);
            $this->waitForText($product['supplier']);
        }
    }
}
