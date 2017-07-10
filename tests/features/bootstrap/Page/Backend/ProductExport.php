<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Helper\ContextAwarePage;
use Shopware\Tests\Mink\HelperSelectorInterface;

class ProductExport extends ContextAwarePage implements HelperSelectorInterface
{
    private $feedConfigurationLabel = 'Feed - Konfiguration';
    private $overviewWindowLabel = 'Produktexporte';
    private $startCategoryName = 'Deutsch';

    /**
     * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Fills in and submits the configuration form
     *
     * @param array $formData Defines the form field and their data
     */
    public function fillConfigurationForm($formData)
    {
        $builder = new BackendXpathBuilder();
        $feedConfigWindowXpath = BackendXpathBuilder::getWindowXpathByTitle($this->feedConfigurationLabel, false);

        foreach ($formData as $entry) {
            /** @var NodeElement $window */
            $window = $this->find('xpath', $feedConfigWindowXpath);

            switch ($entry['type']) {
                case 'input':
                    $this->fillInput($window, $entry['label'], $entry['value']);
                    break;
                case 'combobox':
                    $this->fillCombobox($window, $entry);
                    break;
                case 'checkbox':
                    $this->fillCheckbox($window, $entry);
                    break;
                case 'selecttree':
                    $this->fillSelecttree($window, $entry);
                    break;
                case 'textarea':
                    $field = $window->find('xpath', BackendXpathBuilder::getFormElementXpathByLabel($entry['label'], 'textarea'));
                    $field->setValue($entry['value']);
                    break;
            }
        }
    }

    /**
     * Helper method to fill in an extJS input field
     *
     * @param NodeElement $window
     * @param string $label Label of the input field
     * @param string $value
     */
    private function fillInput(NodeElement $window, $label, $value)
    {
        $inputXpath = (new BackendXpathBuilder())->reset()->getInputXpathByLabel($label);
        $field = $window->find('xpath', $inputXpath);
        $field->setValue($value);
    }

    /**
     * Fills in a combobox
     *
     * @param NodeElement $window
     * @param array $entry Data which should be used for the combobox
     */
    private function fillCombobox($window, $entry)
    {
        $builder = new BackendXpathBuilder();

        $pebble = $window->find('xpath', $builder->getSelectorPebbleXpathByLabel($entry['label']));
        $this->assertNotNull($pebble, print_r($entry, true));
        $pebble->click();

        $optionXP = $builder->reset()->getDropdownXpathByAction($entry['action'], $entry['value']);
        $this->waitForXpathElementPresent($optionXP);
        $option = $this->find('xpath', $optionXP);

        $this->clickOnElementWhenReady($option);
    }

    /**
     * Selects an entry in a selecttree
     *
     * @param NodeElement $window Window, which is currently opened
     * @param array $entry Data which should be used for the selection
     */
    private function fillSelecttree($window, $entry)
    {
        $builder = new BackendXpathBuilder();

        $categoryXpaths = $builder->getSelectTreeElementXpaths($entry['value']);

        $pebble = $window->find('xpath', $builder->reset()->getSelectorPebbleXpathByLabel($entry['label']));
        $pebble->click();

        foreach ($categoryXpaths as $xpath) {
            $dropdownXpath = $builder
                ->reset()
                ->descendant('div', ['@text' => $this->startCategoryName])
                ->ancestor('div', ['~class' => 'x-tree-panel'])
                ->getXpath();
            $this->waitForXpathElementPresent($dropdownXpath);

            $dropdown = $this->find('xpath', $dropdownXpath);
            $option = $dropdown->find('xpath', $xpath);
            $option->click();
        }
    }

    /**
     * Fills in a checkbox
     *
     * @param NodeElement $window
     * @param array $entry Data which should be used for the checkbox
     */
    private function fillCheckbox($window, $entry)
    {
        $builder = new BackendXpathBuilder();

        $checkboxXpath = $builder->reset()->getInputXpathByLabel($entry['label']);

        $field = $window->find('xpath', $checkboxXpath);
        $field->click();
    }

    /**
     * Enters the template on which the product export is based of
     *
     * @param string $template The template itself
     */
    public function enterTemplate($template)
    {
        $builder = new BackendXpathBuilder();
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($this->feedConfigurationLabel);

        $window = $this->find('xpath', $windowXpath);

        $templateAreaXpath = $builder->reset()->child('div', ['~class' => 'cm-s-default'])->getXpath();
        $textareaXpath = $builder->reset()->descendant('textarea', [], 1)->getXpath();
        $this->waitForXpathElementPresent($templateAreaXpath);

        $templateArea = $window->find('xpath', $templateAreaXpath);
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
        $builder = new BackendXpathBuilder();
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($this->overviewWindowLabel);

        $window = $this->find('xpath', $windowXpath);

        $exportRowXpath = $builder
            ->reset()
            ->child('div', ['@text' => 'Erster Test-Export'])
            ->ancestor('tr', [], 1)
            ->getXpath();

        $exportRow = $window->find('xpath', $exportRowXpath);

        $startIconXpath = $builder->reset()->child('img', ['~class' => 'sprite-lightning'])->getXpath();
        $startIcon = $exportRow->find('xpath', $startIconXpath);

        $this->assertNotNull($startIcon, print_r($startIcon->getXpath(), true));

        $startIcon->click();
    }

    /**
     * Open a product export byt
     *
     * @param $exportTitle
     */
    public function openExport($exportTitle)
    {
        $builder = new BackendXpathBuilder();
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($this->overviewWindowLabel);

        $window = $this->find('xpath', $windowXpath);

        $exportRowXpath = $builder->reset()->child('div', ['@text' => $exportTitle])->ancestor('tr', [], 1)->getXpath();
        $exportRow = $window->find('xpath', $exportRowXpath);

        $fileLinkXpath = $builder->reset()->descendant('a', [], 1)->getXpath();
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
