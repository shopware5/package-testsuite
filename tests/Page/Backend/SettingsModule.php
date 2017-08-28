<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;

class SettingsModule extends BackendModule
{
    /**
     * Clicks on an element in the menu
     *
     * @param string $elementName Name of the element
     */
    public function clickOnMenuElement($elementName)
    {
        $xpath = BackendXpathBuilder::create()
            ->child('span', ['@text' => $elementName, 'and', '~class' => 'x-menu-item-text'])
            ->ancestor('a', [], 1)
            ->getXpath();

        $this->waitForXpathElementPresent($xpath);
        $element = $this->find('xpath', $xpath);
        $element->click();
    }

    /**
     * Clicks on an element in the side menu of the basic settings
     *
     * @param string $elementName Name of the element
     */
    public function clickOnSettingsMenuElement($elementName)
    {
        $xpath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $elementName, 'and', '~class' => 'x-grid-cell-inner'])
            ->ancestor('td', [], 1)
            ->getXpath();

        $this->waitForXpathElementPresent($xpath);
        $element = $this->find('xpath', $xpath);
        $element->click();
    }

    /**
     * Fills in and submits the shop configuration form
     *
     * @param array $data Defines the form field and their data
     */
    public function fillShopConfigurationForm(array $data)
    {
        $window = Window::createFromTitle('Grundeinstellungen - Shops', $this->getSession());
        $this->fillExtJsForm($window, $data);

        $submitButton = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Speichern'));
        $submitButton->click();
    }
}
