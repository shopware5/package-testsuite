<?php

namespace Shopware\Element\Backend;

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
     *
     * @param string $title
     * @param bool   $exactTitleMatch
     *
     * @return Window
     */
    public static function createFromTitle($title, Session $session, $exactTitleMatch = true)
    {
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($title, $exactTitleMatch);
        $window = new Window($windowXpath, $session);

        return $window;
    }

    /**
     * Get a grid view from within the current window.
     * If a window contains multiple grid views, the search can be limited
     * by providing an arbitrary string that appears within the grid view that
     * is to be selected.
     *
     * @param string|null $containsText
     *
     * @return GridView
     */
    public function getGridView($containsText = '')
    {
        $gridView = new GridView($this->getGridViewXpath($containsText), $this->getSession());

        return $gridView;
    }

    /**
     * Get an input form field by its exact label
     *
     *  Example Usage:
     *  $input = $window->getInput('Dateiname:');
     *
     * @param string|null $fieldset
     *
     * @return Input
     */
    public function getInput($label, $fieldset = null)
    {
        $input = new Input($this->getInputXpath($label, $fieldset), $this->getSession());

        return $input;
    }

    /**
     * Get a combobox field by its exact label
     *
     *  Example Usage:
     *  $combobox = $window->getCombobox('Steuersatz:');
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return Combobox
     */
    public function getCombobox($label, $fieldset = null)
    {
        $combobox = new Combobox($this->getComboboxXpath($label, $fieldset), $this->getSession());

        return $combobox;
    }

    /**
     * Get a checkbox field by its exact label
     *
     *  Example Usage:
     *  $checkbox = $window->getCheckbox('Aktiv:');
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return Checkbox
     */
    public function getCheckbox($label, $fieldset = null)
    {
        $checkbox = new Checkbox($this->getInputXpath($label, $fieldset), $this->getSession());

        return $checkbox;
    }

    /**
     * Get a textarea by its exact label
     *
     *  Example Usage:
     *  $textarea = $window->getTextarea('Kurzbeschreibung:');
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return Textarea
     */
    public function getTextarea($label, $fieldset = null)
    {
        $textarea = new Textarea($this->getTextareaXpath($label, $fieldset), $this->getSession());

        return $textarea;
    }

    /**
     * Get a selecttree by its exact label
     *
     *  Example Usage:
     *  $tree = $window->getSelecttree('Kategorie:');
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return Selecttree
     */
    public function getSelecttree($label, $fieldset = null)
    {
        $selecttree = Selecttree::createFromXpath($this->getSelecttreeXpath($label, $fieldset), $this);

        return $selecttree;
    }

    /**
     * @param string $containsText
     *
     * @return string
     */
    private function getGridViewXpath($containsText = '')
    {
        $gridViewXpath = BackendXpathBuilder::create($this->getXpath())
            ->descendant('div', ['~class' => 'x-grid-with-row-lines'])
            ->descendant('*', ['~text' => $containsText])
            ->ancestor('div', ['~class' => 'x-grid-with-row-lines'])
            ->getXpath();

        return $gridViewXpath;
    }

    /**
     * Get xpath for a combobox within the current window, potentially limited to a given fieldset.
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return string
     */
    private function getComboboxXpath($label, $fieldset = null)
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getComboboxXpathByLabel($label, $scope);
    }

    /**
     * Get xpath for an input field within the current window, optionally scoped to a given fieldset
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return string
     */
    private function getInputXpath($label, $fieldset)
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getInputXpathByLabel($label, $scope);
    }

    /**
     * Get xpath to textarea element within the current window, optionally scoped to a given fieldset
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return string
     */
    private function getTextareaXpath($label, $fieldset)
    {
        $scope = $fieldset
            ? BackendXpathBuilder::getFieldsetXpathByLabel($fieldset, $this->getXpath())
            : $this->getXpath();

        return BackendXpathBuilder::getFormElementXpathByLabel($label, 'textarea', $scope);
    }

    /**
     * Return xpath to a given selecttree within the current window
     *
     * @param string      $label
     * @param string|null $fieldset
     *
     * @return string
     */
    private function getSelecttreeXpath($label, $fieldset)
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
}
