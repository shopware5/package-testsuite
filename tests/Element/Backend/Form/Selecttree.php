<?php

declare(strict_types=1);

namespace Shopware\Element\Backend\Form;

use Behat\Mink\Element\NodeElement;
use InvalidArgumentException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;
use Shopware\Element\Backend\Window;

class Selecttree extends ExtJsElement
{
    public static function createFromXpath(string $xpath, Window $window): Selecttree
    {
        return new self($xpath, $window->getSession());
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        if (!\is_string($value)) {
            throw new InvalidArgumentException('Value must be a string');
        }
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
     */
    private function getSelectTreeElementXpaths(string $selectString): array
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

    private function getSelecttreeDropdownXpath(): string
    {
        return BackendXpathBuilder::create()
            ->descendant('div', ['@text' => 'Deutsch'])
            ->ancestor('div', ['~class' => 'x-tree-panel'])
            ->getXpath();
    }

    private function getDropdownFromWindow(string $dropdownXpath): ?NodeElement
    {
        $dropdown = null;
        $this->waitFor(10, function () use (&$dropdown, $dropdownXpath) {
            $dropdown = $this->getSession()->getPage()->find('xpath', $dropdownXpath);

            return $dropdown !== null;
        });

        return $dropdown;
    }
}
