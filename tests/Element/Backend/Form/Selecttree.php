<?php

namespace Shopware\Element\Backend\Form;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;
use Shopware\Element\Backend\Window;

class Selecttree extends ExtJsElement
{
    /**
     * @param string $xpath
     *
     * @return Selecttree
     */
    public static function createFromXpath($xpath, Window $window)
    {
        $selecttree = new self($xpath, $window->getSession());

        return $selecttree;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        // Open dropdown
        $this->click();

        // Get dropdown
        $dropdownXpath = $this->getSelecttreeDropdownXpath();
        $dropdown = $this->getDropdownFromWindow($dropdownXpath);

        // Click on all necessary leafs
        $elementXpaths = $this->getSelectTreeElementXpaths($value);
        foreach ($elementXpaths as $xpath) {
            $option = $dropdown->find('xpath', $xpath);
            $option->click();
        }
    }

    /**
     * Return a list of all elements of a select tree
     *
     * @param string $selectString
     *
     * @return array
     */
    private function getSelectTreeElementXpaths($selectString)
    {
        $xpaths = [];

        $categories = array_map('trim', explode('>', $selectString));
        $lastKey = \count($categories) - 1;

        foreach ($categories as $key => $category) {
            if ($key === $lastKey) {
                $xpaths[] = BackendXpathBuilder::create()
                    ->descendant('div', ['@text' => $category])
                    ->descendant('img', ['~class' => 'x-tree-icon'])
                    ->getXpath();
                break;
            }

            $xpaths[] = BackendXpathBuilder::create()
                ->descendant('div', ['~class' => 'x-tree-panel'])
                ->descendant('div', ['@text' => $category])
                ->descendant('img', ['~class' => 'x-tree-expander'])
                ->getXpath();
        }

        return $xpaths;
    }

    /**
     * @return string
     */
    private function getSelecttreeDropdownXpath()
    {
        $dropdownXpath = BackendXpathBuilder::create()
            ->descendant('div', ['@text' => 'Deutsch'])
            ->ancestor('div', ['~class' => 'x-tree-panel'])
            ->getXpath();

        return $dropdownXpath;
    }

    /**
     * @return NodeElement|null
     */
    private function getDropdownFromWindow($dropdownXpath)
    {
        $dropdown = null;
        $this->waitFor(10, function () use (&$dropdown, $dropdownXpath) {
            $dropdown = $this->getSession()->getPage()->find('xpath', $dropdownXpath);

            return $dropdown !== null;
        });

        return $dropdown;
    }
}
