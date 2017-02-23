<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;
use Shopware\Tests\Mink\ApiContext;

class ShopSettings extends ContextAwarePage implements HelperSelectorInterface
{
    private $shopConfigurationLabel = 'Grundeinstellungen - Shops';
    private $saveShopConfigurationLabel = 'Speichern';


    /**
     * * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Clicks on an element in the menu
     *
     * @param string $elementName Name of the element
     */
    public function clickOnMenuElement($elementName)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $elementXpath = $xp->span(['@text' => $elementName, 'and', '~class' => 'x-menu-item-text'])->a('asc', [], 1)->get();
        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }

    /**
     * Clicks on an element in the side menu of the basic settings
     *
     * @param string $elementName Name of the element
     */
    public function clickOnSettingsMenuElement($elementName)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();
        $elementXpath = $xp->div(['@text' => $elementName, 'and', '~class' => 'x-grid-cell-inner'])->td('asc', [], 1)->get();
        $element = $this->find('xpath', $elementXpath);

        $element->click();
    }

    /**
     * Fills in and submits a configuration form in the basic configuration
     *
     * @param string[] $data Defines the form field and their data
     */
    public function fillConfigurationForm($data)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();
        $windowXP = $xp->xWindowByExactTitle($this->shopConfigurationLabel)->get();

        foreach ($data as $entry) {

            /** @var NodeElement $window */
            $window = $this->find('xpath', $windowXP);

            switch ($entry['type']) {
                case 'input':
                    $field = $window->find('xpath', $xp->getXInputForLabel($entry['label']));
                    $field->setValue($entry['value']);
                    break;
                case 'combobox':
                    $this->fillComboBox($window, $entry, $xp);
                    break;
                case 'checkbox':
                    $checkboxXpath = $xp->getXFormElementForLabel($entry['label'], 'input');
                    $tableXpath = $checkboxXpath . $xp->table('asc', [], 1)->get();

                    $field = $window->find('xpath', $tableXpath);
                    $field->click();

                    $table = $this->find('xpath', $tableXpath);

                    $this->spin(function () use ($tableXpath, $table) {
                        if ($table !== null && $table->hasClass('x-form-cb-checked')) {
                            return true;
                        }
                        return false;
                    }, 5);
                    break;
                case 'selecttree':
                    $this->fillSelectTree($window, $entry, $xp);
                    break;
                case 'textarea':
                    $field = $window->find('xpath', $xp->getXFormElementForLabel($entry['label'], 'textarea'));
                    $field->setValue($entry['value']);
                    break;
            }
        }
        $this->submitForm($this->saveShopConfigurationLabel, $xp);
    }

    /**
     * Fills in a combobox
     *
     * @param NodeElement $window
     * @param string[] $entry Data which should be used for the combobox
     * @param XpathBuilder $xp Window, which is currently opened
     */
    private function fillComboBox($window, $entry, $xp)
    {
        $pebble = $window->find('xpath', $xp->getXSelectorPebbleForLabel($entry['label']));
        $this->assertNotNull($pebble, print_r($entry, true));
        $pebble->click();

        $this->waitForSelectorInvisible('xpath', $xp->xDropdown($entry['action'])->get() . "/descendant::*[contains(text(), 'Lade Daten')][1]");

        $optionXP = $xp->xDropdown($entry['action'], $entry['value'])->get();

        $option = $this->find('xpath', $optionXP);

        $this->assertNotNull($option, print_r($entry, true));
        $this->clickOnElementWhenReady($option);
    }

    /**
     * Selects an entry in a SelectTree
     *
     * @param NodeElement $window Window, which is currently opened
     * @param string[] $entry Data which should be used for the selection
     * @param XpathBuilder $xp
     */
    private function fillSelectTree($window, $entry, $xp)
    {
        $xpathsCategory = $xp->getSelectTreeElements($entry['value']);

        $pebble = $window->find('xpath', $xp->getXSelectorPebbleForLabel($entry['label']));
        $pebble->click();

        foreach ($xpathsCategory as $xpath) {
            $dropdownXP = $xp->div('desc', ['@text' => 'Deutsch'])->div('asc', ['~class' => 'x-tree-panel'])->get();
            $this->waitForXpathElementPresent($dropdownXP);

            $dropdown = $this->find('xpath', $dropdownXP);
            $this->assertNotNull($dropdown, print_r($entry, true));

            $option = $dropdown->find('xpath', $xpath);
            $this->assertNotNull($option, print_r($entry, true));
            $option->click();
        }
    }

    /**
     * Clicks a specific element
     *
     * @param string $elementName Name of the element
     * @param XpathBuilder $xp
     */
    public function submitForm($elementName, $xp)
    {
        $elementXpath = $xp->span(['@text' => $elementName, 'and', '~class' => 'x-btn-inner'])->button('asc', [], 1)->get();

        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }
}