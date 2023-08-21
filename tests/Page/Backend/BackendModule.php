<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;
use Shopware\Page\ContextAwarePage;

class BackendModule extends ContextAwarePage
{
    protected string $moduleWindowTitle = '';

    protected ?Window $moduleWindow = null;

    protected string $editorWindowTitle = '';

    protected ?Window $editorWindow = null;

    /**
     * Fill a standard ExtJs form with data
     *
     * The $formParent should be a NodeElement that can act as a scoped parent for this method, such
     * as the parent ExtJs window. $formElements needs to be an associative array containing the following
     * keys:
     *
     * - label (required)    - The *exact* label of the form element
     * - value (required)    - The value this field is supposed to hold
     * - type (required)     - The type of input field; possible values are 'input', 'combobox', 'checkbox',
     *                         'textarea'
     * - fieldset (optional) - You can scope a single form element further by providing the parenting fieldset
     *
     * @param array<array<string, mixed>> $formElements
     *
     * @throws \Exception
     */
    public function fillExtJsForm(Window $formParent, array $formElements): void
    {
        foreach ($formElements as $element) {
            $element['fieldset'] = isset($element['fieldset']) ? $element['fieldset'] : '';
            switch ($element['type']) {
                case 'input':
                    $input = $formParent->getInput($element['label'], $element['fieldset']);
                    $input->setValue($element['value']);
                    break;
                case 'combobox':
                    $combobox = $formParent->getCombobox($element['label'], $element['fieldset']);
                    $combobox->setValue($element['value']);
                    break;
                case 'checkbox':
                    $checkbox = $formParent->getCheckbox($element['label'], $element['fieldset']);
                    $checkbox->toggle();
                    break;
                case 'textarea':
                    $textarea = $formParent->getTextarea($element['label'], $element['fieldset']);
                    $textarea->setValue($element['value']);
                    break;
                case 'selecttree':
                    $selecttree = $formParent->getSelecttree($element['label'], $element['fieldset']);
                    $selecttree->setValue($element['value']);
                    break;
                case 'comboinput':
                    $inputXpath = BackendXpathBuilder::getInputXpathByLabel($element['label']);
                    $supplierInput = $formParent->find('xpath', $inputXpath);
                    $this->fillComboboxInput($supplierInput, $element['value']);
                    break;
            }
        }
    }

    /**
     * Helper method that fills an extJS combobox input field
     */
    public function fillComboboxInput(NodeElement $comboInputField, string $value): void
    {
        $comboInputField->click();
        $comboInputField->setValue($value);
    }

    /**
     * Expands a collapsed element
     */
    public function expandCategoryCollapsible(string $label): void
    {
        $collapsibleFieldXpath = (new BackendXpathBuilder())
            ->child('div', ['@text' => $label], 1)
            ->ancestor('tr', [], 1)
            ->getXpath();

        $element = $this->find('xpath', $collapsibleFieldXpath);

        $element->doubleClick();
    }

    /**
     * Chooses the desired answer in a message box
     *
     * @throws \Exception
     */
    public function answerMessageBox(string $answer): void
    {
        $answerButtonXpath = (new BackendXpathBuilder())
            ->child('span', ['@text' => $answer, 'and', '~class' => 'x-btn-inner'], 1)
            ->ancestor('button', [], 1)
            ->getXpath();

        $answerButton = $this->find('xpath', $answerButtonXpath);
        $answerButton->click();
    }

    /**
     * Helper method that returns the current module window
     */
    protected function getModuleWindow(bool $exactMatch = true): Window
    {
        // Cache the window reference as long as it is still valid
        if (!$this->moduleWindow || !$this->moduleWindow->isValid() || !$exactMatch) {
            $this->moduleWindow = Window::createFromTitle($this->moduleWindowTitle, $this->getSession(), $exactMatch);
        }

        return $this->moduleWindow;
    }

    /**
     * Helper method that returns the current editor window
     */
    protected function getEditorWindow(bool $exactMatch = true): Window
    {
        // Cache the window reference as long as it is still valid
        if (!$this->editorWindow || !$this->editorWindow->isValid() || !$exactMatch) {
            $this->editorWindow = Window::createFromTitle($this->editorWindowTitle, $this->getSession(), $exactMatch);
        }

        return $this->editorWindow;
    }

    /**
     * Clicks the selected icon for the entry with a given name.
     *
     * @throws \Exception
     */
    public function clickEntryIconByName(string $name, string $icon): void
    {
        $editIconXpath = (new BackendXpathBuilder())
            ->child('div')
            ->contains($name)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => $icon])
            ->getXpath();

        $this->waitForXpathElementPresent($editIconXpath);
        $editIcon = $this->find('xpath', $editIconXpath);
        $editIcon->click();
    }
}
