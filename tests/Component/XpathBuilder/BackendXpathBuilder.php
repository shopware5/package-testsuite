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
     * @param bool   $exactMatch
     *
     * @return string
     */
    public static function getWindowXpathByTitle($title, $exactMatch = true)
    {
        $prefix = $exactMatch ? '@' : '~';

        return (new self())
            ->child('span', [$prefix . 'text' => $title])
            ->ancestor('div', ['~class' => 'x-window'], 1)
            ->getXpath();
    }

    /**
     * Get an xpath for an extJS form element by its label
     *
     * @param string $label
     * @param string $tag
     * @param string $scope
     *
     * @return string
     */
    public static function getFormElementXpathByLabel($label, $tag, $scope = '/')
    {
        return static::create($scope)
            ->descendant('label', ['@text' => $label])
            ->ancestor('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant($tag)
            ->getXpath();
    }

    /**
     * Return an Xpath that finds a button by its label
     *
     * @param string $label
     * @param string $scope
     *
     * @return string
     */
    public static function getButtonXpathByLabel($label, $scope = '/')
    {
        return static::create($scope)
            ->child('span', ['@class' => 'x-btn-inner'])
            ->contains($label)
            ->ancestor('button')
            ->getXpath();
    }

    /**
     * Shorthand function to get an extJS input field by its label
     *
     * @param string $label
     * @param string $scope
     *
     * @return string
     */
    public static function getInputXpathByLabel($label, $scope = '/')
    {
        return self::getFormElementXpathByLabel($label, 'input', $scope);
    }

    /**
     * Returns label-specific xpath for a combobox
     *
     * @param string $label
     * @param string $scope
     *
     * @return string
     */
    public static function getComboboxXpathByLabel($label, $scope = '/')
    {
        return static::create($scope)
            ->descendant('label', ['@text' => $label])
            ->ancestor('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant('div', ['~class' => 'x-form-trigger'])
            ->ancestor('tr', [], 2)
            ->getXpath();
    }

    /**
     * Return xpath to the currently focused extJs input
     *
     * @return string
     */
    public static function getFocusedElementXpath()
    {
        return (new self())->child('input', ['~class' => 'x-form-focus'])->getXpath();
    }

    /**
     * Return xpath to an extJs tab by its label
     *
     * @param string $label
     *
     * @return string
     */
    public static function getTabXpathByLabel($label)
    {
        return (new self())
            ->child('span', ['@text' => $label])
            ->ancestor('div', ['~class' => 'x-tab'], 1)
            ->getXpath();
    }

    /**
     * Return xpath to extJs icon by type
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getIconXpathByType($type)
    {
        switch ($type) {
            case 'edit':
                return (new self())->child('img', ['~class' => 'sprite-pencil'])->getXpath();
                break;
            case 'delete':
                return (new self())->child('img', ['~class' => 'sprite-minus-circle-frame'])->getXpath();
                break;
            default:
                throw new \Exception('Unknown icon type ' . $type);
        }
    }

    /**
     * Return a dropdown xpath by its action
     *
     * @param string $action
     * @param string $optionText
     *
     * @return string
     */
    public function getDropdownXpathByAction($action, $optionText = '')
    {
        $this->child('div', ['~class' => 'x-boundlist', 'and', '@data-action' => $action]);

        return empty($optionText)
            ? $this->descendant('li', ['@role' => 'option'])->getXpath()
            : $this->descendant('li', ['@role' => 'option', 'and', '@text' => $optionText])->getXpath();
    }

    /**
     * Return an Xpath that finds a fieldset by its label
     *
     * @param string $label
     * @param string $scope
     *
     * @return string
     */
    public static function getFieldsetXpathByLabel($label, $scope = '/')
    {
        return static::create($scope)
            ->descendant('fieldset')
            ->descendant('legend')
            ->descendant('div')
            ->contains($label)
            ->ancestor('fieldset')
            ->getXpath();
    }
}
