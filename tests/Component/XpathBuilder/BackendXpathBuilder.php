<?php

namespace Shopware\Component\XpathBuilder;

class BackendXpathBuilder extends BaseXpathBuilder
{
    /**
     * Get an xpath for an extJS window by its title
     *
     * This function builds an xpath that matches the window name exactly, but
     * allows explicit fuzziness by passing in 'false' as the second parameter.
     *
     * @param string $title
     * @param bool $exactMatch
     * @return string
     */
    public static function getWindowXpathByTitle($title, $exactMatch = true)
    {
        $prefix = $exactMatch ? '@' : '~';
        return (new self)->child('span', [$prefix . 'text' => $title])->ancestor('div', ['~class' => 'x-window'], 1)->getXpath();
    }

    /**
     * Get an xpath for an extJS form element by its label
     *
     * @param string $label
     * @param string $tag
     * @return string
     */
    public static function getFormElementXpathByLabel($label, $tag)
    {
        return (new self)->descendant('label', ['@text' => $label])
            ->ancestor('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant($tag)
            ->getXpath();
    }

    /**
     * Shorthand function to get an extJS input field by its label
     *
     * @param string $label
     * @return string
     */
    public function getInputXpathByLabel($label)
    {
        return self::getFormElementXpathByLabel($label, 'input');
    }

    /**
     * Return a selector pebble xpath by its associated label
     *
     * @param string $label
     * @return string
     */
    public function getSelectorPebbleXpathByLabel($label)
    {
        return $this
            ->descendant('label', ['@text' => $label])
            ->ancestor('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant('div', ['~class' => 'x-form-trigger'])
            ->getXpath();
    }

    public function getComboboxXpathByLabel($label)
    {
        $builder = new BackendXpathBuilder();
        $builder->setXpath($this->getSelectorPebbleXpathByLabel($label));

        return $builder
            ->ancestor('tr', [], 2)
            ->getXpath();
    }

    /**
     * Return a dropdown xpath by its action
     *
     * @param string $action
     * @param string $optionText
     * @return string
     */
    public function getDropdownXpathByAction($action, $optionText = "")
    {
        $this->child('div', ['~class' => 'x-boundlist', 'and', '@data-action' => $action]);

        return empty($optionText)
            ? $this->descendant('li', ['@role' => 'option'])->getXpath()
            : $this->descendant('li', ['@role' => 'option', 'and', '@text' => $optionText])->getXpath();
    }

    /**
     * Return a list of all elements of a select tree
     *
     * @param string $selectString
     * @return array
     */
    public function getSelectTreeElementXpaths($selectString)
    {
        $xpaths = [];

        $categories = array_map('trim', explode('>', $selectString));
        $lastKey = count($categories) - 1;

        foreach ($categories as $key => $category) {
            if ($key === $lastKey) {
                $xpaths[] = $this
                    ->descendant('div', ['@text' => $category])
                    ->descendant('img', ['~class' => 'x-tree-icon'])
                    ->getXpath();
                break;
            }

            $xpaths[] = $this
                ->descendant('div', ['~class' => 'x-tree-panel'])
                ->descendant('div', ['@text' => $category])
                ->descendant('img', ['~class' => 'x-tree-expander'])
                ->getXpath();
        }

        return $xpaths;
    }

    public function getButtonXpathByLabel($label) {
        return (new BackendXpathBuilder())
            ->child('span', ['@class' => 'x-btn-inner'])
            ->contains($label)
            ->ancestor('button')
            ->getXpath();
    }

    /**
     * Return an Xpath that finds a fieldset by it's label
     *
     * @param string $label
     * @return string
     */
    public function getFieldsetXpathByLabel($label)
    {
        return (new BackendXpathBuilder())
            ->child('fieldset')
            ->descendant('legend')
            ->descendant('div')
            ->contains($label)
            ->ancestor('fieldset')
            ->getXpath();
    }
}