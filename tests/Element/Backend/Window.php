<?php

declare(strict_types=1);

namespace Shopware\Element\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Form\Checkbox;
use Shopware\Element\Backend\Form\Combobox;
use Shopware\Element\Backend\Form\Input;
use Shopware\Element\Backend\Form\Selecttree;
use Shopware\Element\Backend\Form\Textarea;
use Shopware\Element\Backend\GridView\GridView;

/**
 * Representing an ExtJS window, the fundamental block of an ExtJs application.
 *
 * You can get other ExtJS elements from a window, e.g. grid views, form inputs
 * and buttons.
 */
class Window extends ExtJsElement
{
    /**
     * Static construction method for creating an ExtJS Window object.
     *
     * Example Usage:
     *  $window = Window::createFromTitle('Kundenadministration', $this->getSession());
     */
    public static function createFromTitle(string $title, Session $session, bool $exactTitleMatch = true): Window
    {
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($title, $exactTitleMatch);

        return new Window($windowXpath, $session);
    }

    /**
     * Get a grid view from within the current window.
     * If a window contains multiple grid views, the search can be limited
     * by providing an arbitrary string that appears within the grid view that
     * is to be selected.
     */
    public function getGridView(?string $containsText = ''): GridView
    {
        return new GridView($this->getGridViewXpath($containsText), $this->getSession());
    }

    /**
     * Get an input form field by its exact label
     *
     *  Example Usage:
     *  $input = $window->getInput('Dateiname:');
     */
    public function getInput(string $label, ?string $fieldset = null): Input
    {
        return new Input($this->getInputXpath($label, $fieldset), $this->getSession());
    }

    /**
     * Get a combobox field by its exact label
     *
     *  Example Usage:
     *  $combobox = $window->getCombobox('Steuersatz:');
     */
    public function getCombobox(string $label, ?string $fieldset = null): Combobox
    {
        return new Combobox($this->getComboboxXpath($label, $fieldset), $this->getSession());
    }

    /**
     * Get a checkbox field by its exact label
     *
     *  Example Usage:
     *  $checkbox = $window->getCheckbox('Aktiv:');
     */
    public function getCheckbox(string $label, ?string $fieldset = null): Checkbox
    {
        return new Checkbox($this->getInputXpath($label, $fieldset), $this->getSession());
    }

    /**
     * Get a textarea by its exact label
     *
     *  Example Usage:
     *  $textarea = $window->getTextarea('Kurzbeschreibung:');
     */
    public function getTextarea(string $label, ?string $fieldset = null): Textarea
    {
        return new Textarea($this->getTextareaXpath($label, $fieldset), $this->getSession());
    }

    /**
     * Get a selecttree by its exact label
     *
     *  Example Usage:
     *  $tree = $window->getSelecttree('Kategorie:');
     */
    public function getSelecttree(string $label, ?string $fieldset = null): Selecttree
    {
        return Selecttree::createFromXpath($this->getSelecttreeXpath($label, $fieldset), $this);
    }

    private function getGridViewXpath(string $containsText = ''): string
    {
        return BackendXpathBuilder::create($this->getXpath())
            ->descendant('div', ['~class' => 'x-grid-with-row-lines'])
            ->descendant('*', ['~text' => $containsText])
            ->ancestor('div', ['~class' => 'x-grid-with-row-lines'])
            ->getXpath();
    }

    /**
     * Get xpath for a combobox within the current window, potentially limited to a given fieldset.
     */
    private function getComboboxXpath(string $label, ?string $fieldset = null): string
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getComboboxXpathByLabel($label, $scope);
    }

    /**
     * Get xpath for an input field within the current window, optionally scoped to a given fieldset
     */
    private function getInputXpath(string $label, ?string $fieldset): string
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getInputXpathByLabel($label, $scope);
    }

    /**
     * Get xpath to textarea element within the current window, optionally scoped to a given fieldset
     */
    private function getTextareaXpath(string $label, ?string $fieldset): string
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getFormElementXpathByLabel($label, 'textarea', $scope);
    }

    /**
     * Return xpath to a given selecttree within the current window
     */
    private function getSelecttreeXpath(string $label, ?string $fieldset): string
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::create($scope)
            ->descendant('label', ['@text' => $label])
            ->ancestor('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant('div', ['~class' => 'x-form-trigger'])
            ->getXpath();
    }

    /**
     * @param string          $selector
     * @param string[]|string $locator
     *
     * @throws ElementNotFoundException
     */
    public function find($selector, $locator): NodeElement
    {
        $element = parent::find($selector, $locator);
        if ($element === null) {
            if (\is_array($locator)) {
                $locator = implode(' ', $locator);
            }
            throw new ElementNotFoundException($this->getDriver(), null, $selector, $locator);
        }

        return $element;
    }
}
