<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Helper\ContextAwarePage;

class BackendModule extends ContextAwarePage
{
    /**
     * Helper method that fills an extJS input field
     *
     * @param NodeElement $input
     * @param string $value
     */
    public function fillInput(NodeElement $input, $value)
    {
        $input->setValue($value);
    }

    /**
     * Helper method that fills an extJS combobox
     *
     * @param NodeElement $combobox
     * @param string $value
     * @throws \Exception
     */
    public function fillCombobox(NodeElement $combobox, $value)
    {
        $builder = new BackendXpathBuilder();

        $pebble = $combobox->find('xpath', $builder->child('div', ['~class' => 'x-form-trigger'])->getXpath());

        if (!$pebble->isVisible()) {
            throw new \Exception('Pebble with for combobox with value ' . $value . 'not visible.');
        }

        $pebble->click();

        $pebbleId = $pebble->getAttribute('id');
        $pebbleY = $this->getYCoordinateForElement($pebbleId, 'bottom');

        sleep(1);

        $dropdowns = $this->findAll('xpath',
            $builder->reset()->child('div', ['~class' => 'x-boundlist'])->getXpath());

        /** @var NodeElement $dropdown */
        foreach ($dropdowns as $dropdown) {
            $dropdownY = $this->getYCoordinateForElement($dropdown->getAttribute('id'));

            // Compare on-screen position to match dropdown and pebble that are unrelated in the DOM
            if (abs($pebbleY - $dropdownY) < 5) {
                $option = $dropdown->find('xpath',
                    $builder->reset()->child('li', ['@role' => 'option', 'and', '@text' => $value])->getXpath());
                $option->click();
                break;
            }
        }
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
}