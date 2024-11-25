<?php

declare(strict_types=1);

namespace Shopware\Element\Backend\Form;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

class Combobox extends ExtJsElement
{
    /**
     * {@inheritdoc}
     */
    public function setValue($value): void
    {
        // Open combobox dropdown
        $pebble = $this->getComboboxPebble();
        $pebble->click();

        // Find the correct dropdown by its positioning on the page
        foreach ($this->getOpenDropdowns() as $dropdown) {
            if (!$this->elementsTouch($dropdown, $pebble)) {
                continue;
            }

            // Click on correct dropdown entry
            $option = $this->getOptionByValue($value, $dropdown);
            if (!$option instanceof NodeElement) {
                throw new \RuntimeException(\sprintf('Could not find option with value "%s"', print_r($value, true)));
            }

            $option->click();
        }
    }

    /**
     * Helper method that returns true if two NodeElements touch
     */
    private function elementsTouch(NodeElement $elemA, NodeElement $elemB): bool
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
     * @param string $side Can be either top, bottom, left or right
     */
    private function getYCoordinateForElement(string $id, string $side = 'top'): int
    {
        return (int) $this->getSession()->getDriver()->evaluateScript(
            "return document.getElementById('" . $id . "').getBoundingClientRect()." . $side . ';'
        );
    }

    /**
     * @throws \Exception
     */
    private function getComboboxPebble(): NodeElement
    {
        $pebbleXpath = BackendXpathBuilder::create()->child('div', ['~class' => 'x-form-trigger'])->getXpath();
        $pebble = $this->find('xpath', $pebbleXpath);

        if (!$pebble->isVisible()) {
            throw new \Exception('Pebble for combobox not visible.');
        }

        return $pebble;
    }

    private function getDropdownsXpath(): string
    {
        return BackendXpathBuilder::create()->child('div', ['~class' => 'x-boundlist'])->getXpath();
    }

    /**
     * Find all open dropdowns on page
     *
     * @return NodeElement[]
     */
    private function getOpenDropdowns(): array
    {
        sleep(2);

        $dropdownsXpath = $this->getDropdownsXpath();

        return $this->getSession()->getPage()->findAll('xpath', $dropdownsXpath);
    }

    private function getOptionByValue(string $value, NodeElement $dropdown): ?NodeElement
    {
        $optionXpath = BackendXpathBuilder::create()
            ->child('li', ['@role' => 'option', 'and', '@text' => $value])
            ->getXpath();

        return $dropdown->find('xpath', $optionXpath);
    }
}
