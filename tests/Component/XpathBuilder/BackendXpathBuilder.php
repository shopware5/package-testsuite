<?php

declare(strict_types=1);

namespace Shopware\Component\XpathBuilder;

class BackendXpathBuilder extends BaseXpathBuilder
{
    /**
     * Get an xpath for an extJS window by its title
     *
     * This function builds an xpath that matches the window name exactly, but
     * allows explicit fuzziness by passing in 'false' as the second parameter.
     */
    public static function getWindowXpathByTitle(string $title, bool $exactMatch = true): string
    {
        $prefix = $exactMatch ? '@' : '~';

        return (new self())
            ->child('span', [$prefix . 'text' => $title])
            ->ancestor('div', ['~class' => 'x-window'], 1)
            ->getXpath();
    }

    /**
     * Get an xpath for an extJS form element by its label
     */
    public static function getFormElementXpathByLabel(string $label, string $tag, string $scope = '/'): string
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
     */
    public static function getButtonXpathByLabel(string $label, string $scope = '/'): string
    {
        return static::create($scope)
            ->child('span', ['@class' => 'x-btn-inner'])
            ->contains($label)
            ->ancestor('button')
            ->getXpath();
    }

    /**
     * Shorthand function to get an extJS input field by its label
     */
    public static function getInputXpathByLabel(string $label, string $scope = '/'): string
    {
        return self::getFormElementXpathByLabel($label, 'input', $scope);
    }

    /**
     * Returns label-specific xpath for a combobox
     */
    public static function getComboboxXpathByLabel(string $label, string $scope = '/'): string
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
     */
    public static function getFocusedElementXpath(): string
    {
        return (new self())->child('input', ['~class' => 'x-form-focus'])->getXpath();
    }

    /**
     * Return xpath to an extJs tab by its label
     */
    public static function getTabXpathByLabel(string $label): string
    {
        return (new self())
            ->child('span', ['@text' => $label])
            ->ancestor('div', ['~class' => 'x-tab'], 1)
            ->getXpath();
    }

    /**
     * Return xpath to extJs icon by type
     *
     * @throws \Exception
     */
    public static function getIconXpathByType(string $type): string
    {
        switch ($type) {
            case 'edit':
                return (new self())->child('img', ['~class' => 'sprite-pencil'])->getXpath();
                break;
            case 'delete':
                return (new self())->child('img', ['~class' => 'sprite-minus-circle-frame'])->getXpath();
                break;
            default:
                throw new \RuntimeException('Unknown icon type ' . $type);
        }
    }

    /**
     * Return an Xpath that finds a fieldset by its label
     */
    public static function getFieldsetXpathByLabel(string $label, string $scope = '/'): string
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
