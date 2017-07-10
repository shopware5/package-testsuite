<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Component\XpathBuilder\LegacyXpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class ShopSettings extends ContextAwarePage implements HelperSelectorInterface
{
    private $shopConfigurationLabel = 'Grundeinstellungen - Shops';
    private $saveShopConfigurationLabel = 'Speichern';
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
     * Clicks on an element in the menu
     *
     * @param string $elementName Name of the element
     */
    public function clickOnMenuElement($elementName)
    {
        /** @var LegacyXpathBuilder $xp */
        $xp = new LegacyXpathBuilder();

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
        /** @var LegacyXpathBuilder $xp */
        $xp = new LegacyXpathBuilder();
        $elementXpath = $xp->div(['@text' => $elementName, 'and', '~class' => 'x-grid-cell-inner'])->td('asc', [], 1)->get();
        $element = $this->find('xpath', $elementXpath);

        $element->click();
    }

    /**
     * Fills in and submits the shop configuration form
     *
     * @param string[] $data Defines the form field and their data
     */
    public function fillConfigurationForm($data)
    {
        /** @var LegacyXpathBuilder $xp */
        $xp = new LegacyXpathBuilder();
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
                    $this->fillCombobox($window, $entry, $xp);
                    break;
                case 'checkbox':
                    $this->fillCheckbox($window, $entry, $xp);
                    break;
                case 'selecttree':
                    $this->fillSelecttree($window, $entry, $xp);
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
     * @param string $entry Data which should be used for the combobox
     * @param LegacyXpathBuilder $xp Window, which is currently opened
     */
    private function fillCombobox($window, $entry, $xp)
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
     * Selects an entry in a selecttree
     *
     * @param NodeElement $window Window, which is currently opened
     * @param string $entry Data which should be used for the selection
     * @param LegacyXpathBuilder $xp
     */
    private function fillSelecttree($window, $entry, $xp)
    {
        $xpathsCategory = $xp->getSelectTreeElements($entry['value']);

        $pebble = $window->find('xpath', $xp->getXSelectorPebbleForLabel($entry['label']));
        $pebble->click();

        foreach ($xpathsCategory as $xpath) {
            $dropdownXP = $xp->div('desc', ['@text' => $this->startCategoryName])->div('asc', ['~class' => 'x-tree-panel'])->get();
            $this->waitForXpathElementPresent($dropdownXP);

            $dropdown = $this->find('xpath', $dropdownXP);
            $this->assertNotNull($dropdown, print_r($entry, true));

            $option = $dropdown->find('xpath', $xpath);
            $this->assertNotNull($option, print_r($entry, true));
            $option->click();
        }
    }


    /**
     * Fills in a checkbox
     *
     * @param NodeElement $window
     * @param string $entry Data which should be used for the checkbox
     * @param LegacyXpathBuilder $xp Window, which is currently opened
     */
    private function fillCheckbox($window, $entry, $xp)
    {
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
    }

    /**
     * Clicks the corresponding button to submit a form
     *
     * @param string $elementName Name of the element
     * @param LegacyXpathBuilder $xp
     */
    public function submitForm($elementName, $xp)
    {
        $elementXpath = $xp->span(['@text' => $elementName, 'and', '~class' => 'x-btn-inner'])->button('asc', [], 1)->get();
        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }
}
