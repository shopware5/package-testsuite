<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class ProductExport extends ContextAwarePage implements HelperSelectorInterface
{
    private $feedConfigurationLabel = 'Feed - Konfiguration';
    private $overViewWindowLabel = 'Produktexporte';
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
     * @param array $data Defines the form field and their data
     */
    public function fillConfigurationForm($data)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();
        $windowXP = $xp->xWindowByExactTitle($this->feedConfigurationLabel)->get();

        foreach ($data as $entry) {

            /** @var NodeElement $window */
            $window = $this->find('xpath', $windowXP);

            switch ($entry['type']) {
                case 'input':
                    $field = $window->find('xpath', $xp->getXInputForLabel($entry['label']));
                    $field->setValue($entry['value']);
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
                    $field = $window->find('xpath', $xp->getXFormElementForLabel($entry['label'], 'textarea'));
                    $field->setValue($entry['value']);
                    break;
            }
        }
    }

    /**
     * Fills in a combobox
     *
     * @param NodeElement $window
     * @param array $entry Data which should be used for the combobox
     */
    private function fillCombobox($window, $entry)
    {
        $xp = new XpathBuilder();

        $pebble = $window->find('xpath', $xp->getXSelectorPebbleForLabel($entry['label']));
        $this->assertNotNull($pebble, print_r($entry, true));
        $pebble->click();

        $optionXP = $xp->xDropdown($entry['action'], $entry['value'])->get();
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
        $xp = new XpathBuilder();
        $xpathsCategory = $xp->getSelectTreeElements($entry['value']);

        $pebble = $window->find('xpath', $xp->getXSelectorPebbleForLabel($entry['label']));
        $pebble->click();

        foreach ($xpathsCategory as $xpath) {
            $dropdownXP = $xp->div('desc', ['@text' => $this->startCategoryName])->div('asc', ['~class' => 'x-tree-panel'])->get();
            $this->waitForXpathElementPresent($dropdownXP);

            $dropdown = $this->find('xpath', $dropdownXP);
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
        $xp = new XpathBuilder();

        $checkboxXpath = $xp->getXFormElementForLabel($entry['label'], 'input');
        $tableXpath = $checkboxXpath . $xp->table('asc', [], 1)->get();

        $field = $window->find('xpath', $checkboxXpath);
        $table = $this->find('xpath', $tableXpath);
        $field->click();

        $this->spin(function () use ($tableXpath, $table) {
            if ($table !== null && $table->hasClass('x-form-cb-checked')) {
                return true;
            }
            return false;
        }, 5);
    }

    /**
     * Enters the template on which the product export is based of
     *
     * @param string $template The template itself
     */
    public function enterTemplate($template)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();
        $windowXP = $xp->xWindowByExactTitle($this->feedConfigurationLabel)->get();

        /** @var NodeElement $window */
        $window = $this->find('xpath', $windowXP);

        $templateAreaXpath = $xp->div(['~class' => 'cm-s-default'])->get();
        $textAreaXpath = $xp->textarea('desc', [], 1)->get();
        $this->waitForXpathElementPresent($templateAreaXpath);

        $templateArea = $window->find('xpath', $templateAreaXpath);
        $this->assertNotNull($templateArea, print_r($templateAreaXpath, true));

        $textarea = $templateArea->find('xpath', $textAreaXpath);
        $this->assertNotNull($textarea, print_r($textAreaXpath, true));

        $templateArea->click();
        $textarea->setValue($template);
    }

    public function startExport()
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();
        $windowXP = $xp->xWindowByExactTitle($this->overViewWindowLabel)->get();

        /** @var NodeElement $window */
        $window = $this->find('xpath', $windowXP);

        $exportRowXpath = $xp->div(['@text' => 'Erster Test-Export'])->tr('asc', [], 1)->get();
        $exportRow = $window->find('xpath', $exportRowXpath);

        $startIconXpath = $xp->img(['~class' => 'sprite-lightning'])->get();
        $startIcon = $exportRow->find('xpath', $startIconXpath);
        $this->assertNotNull($startIcon, print_r($startIcon->getXpath(), true));

        $startIcon->click();
    }

    public function openExport($exportTitle)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXP = $xp->xWindowByExactTitle($this->overViewWindowLabel)->get();

        /** @var NodeElement $window */
        $window = $this->find('xpath', $windowXP);

        $exportRowXpath = $xp->div(['@text' => $exportTitle])->tr('asc', [], 1)->get();
        $exportRow = $window->find('xpath', $exportRowXpath);

        $fileLinkXpath = $xp->a('desc', [], 1)->get();
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
