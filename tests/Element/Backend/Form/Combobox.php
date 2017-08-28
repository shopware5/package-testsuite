<?php

namespace Shopware\Element\Backend\Form;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

class Combobox extends ExtJsElement
{
    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        // Open combobox dropdown
        $pebble = $this->getComboboxPebble();
        $pebble->click();

        // Find the correct dropdown by it's positioning on the page
        foreach ($this->getOpenDropdowns() as $dropdown) {
            if (!$this->elementsTouch($dropdown, $pebble)) {
                continue;
            }

            // Click on correct dropdown entry
            $this->getOptionByValue($value, $dropdown)->click();
        }
    }

    /**
     * Helper method that returns true if two NodeElements touch
     *
     * @param NodeElement $elemA
     * @param NodeElement $elemB
     * @return bool
     */
    private function elementsTouch(NodeElement $elemA, NodeElement $elemB)
    {
        $idA = $elemA->getAttribute('id');
        $aTop = $this->getYCoordinateForElement($idA, 'top');
        $aBottom = $this->getYCoordinateForElement($idA, 'bottom');

        $idB = $elemB->getAttribute('id');
        $bTop = $this->getYCoordinateForElement($idB, 'top');
        $bBottom = $this->getYCoordinateForElement($idB, 'bottom');

        return abs($aTop - $bBottom) < 5 || abs($aBottom - $bTop) < 5;
    }

    /**
     * Get the bounding box position value for any element on the page by it's id
     *
     * @param string $id
     * @param string $side Can be either top, bottom, left or right
     * @return int
     */
    private function getYCoordinateForElement($id, $side = 'top')
    {
        return (int)$this->getSession()->getDriver()->evaluateScript(
            "return document.getElementById('" . $id . "').getBoundingClientRect()." . $side . ";"
        );
    }

    /**
     * @return NodeElement
     * @throws \Exception
     */
    private function getComboboxPebble()
    {
        $pebbleXpath = BackendXpathBuilder::create()->child('div', ['~class' => 'x-form-trigger'])->getXpath();
        $pebble = $this->find('xpath', $pebbleXpath);

        if (!$pebble->isVisible()) {
            throw new \Exception('Pebble for combobox not visible.');
        }

        return $pebble;
    }

    /**
     * @return string
     */
    private function getDropdownsXpath()
    {
        return BackendXpathBuilder::create()->child('div', ['~class' => 'x-boundlist'])->getXpath();
    }

    /**
     * Find all open dropdowns on page
     *
     * @return NodeElement[]
     */
    private function getOpenDropdowns()
    {
        sleep(2);

        $dropdownsXpath = $this->getDropdownsXpath();
        $dropdowns = $this->getSession()->getPage()->findAll('xpath', $dropdownsXpath);
        return $dropdowns;
    }

    /**
     * @param string $value
     * @param NodeElement $dropdown
     * @return NodeElement
     */
    private function getOptionByValue($value, NodeElement $dropdown)
    {
        $optionXpath = BackendXpathBuilder::create()
            ->child('li', ['@role' => 'option', 'and', '@text' => $value])
            ->getXpath();

        $option = $dropdown->find('xpath', $optionXpath);
        return $option;
    }
}