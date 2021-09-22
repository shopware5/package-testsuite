<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;
use Shopware\Page\ContextAwarePage;

class BackendModule extends ContextAwarePage
{
    protected string $moduleWindowTitle = '';

    /**
     * @var Window|null
     */
    protected $moduleWindow;

    /**
     * @var string
     */
    protected $editorWindowTitle;

    /**
     * @var Window|null
     */
    protected $editorWindow;

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
     * @throws \Exception
     */
    public function fillExtJsForm(Window $formParent, array $formElements)
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
     *
     * @param string $value
     */
    public function fillComboboxInput(NodeElement $comboInputField, $value)
    {
        $comboInputField->click();
        $comboInputField->setValue($value);
    }

    /**
     * Expands a collapsed element
     *
     * @param string $label
     * @param null   $fieldset
     */
    public function expandCategoryCollapsible($label, $fieldset = null)
    {
        $builder = new BackendXpathBuilder();

        $collapsibleFieldXpath = $builder
            ->child('div', ['@text' => $label], 1)
            ->ancestor('tr', [], 1)
            ->getXpath();

        if ($fieldset) {
            $element = $fieldset->find('xpath', $collapsibleFieldXpath);
        } else {
            $element = $this->find('xpath', $collapsibleFieldXpath);
        }

        $element->doubleClick();
    }

    /**
     * Chooses the desired answer in a message box
     *
     * @throws \Exception
     */
    public function answerMessageBox($answer)
    {
        $builder = new BackendXpathBuilder();

        $answerButtonXpath = $builder
            ->child('span', ['@text' => $answer, 'and', '~class' => 'x-btn-inner'], 1)
            ->ancestor('button', [], 1)
            ->getXpath();

        $answerButton = $this->find('xpath', $answerButtonXpath);
        $this->assertNotNull($answerButton, $answerButtonXpath);
        $answerButton->click();
    }

    public function checkIfTabIsActive($title)
    {
        $builder = new BackendXpathBuilder();

        $tabXpath = $builder
            ->child('span', ['@text' => $title, 'and', '~class' => 'x-tab-inner'], 1)
            ->ancestor('button', ['@disabled' => ''], 1)
            ->getXpath();

        return $tabXpath !== null;
    }

    /**
     * Helper method that returns the current module window
     */
    protected function getModuleWindow(bool $exactMatch = true): Window
    {
        // Cache the window reference as long as it is still valid
        if (!$this->moduleWindow || !$this->moduleWindow->isValid() || $exactMatch === false) {
            $this->moduleWindow = Window::createFromTitle($this->moduleWindowTitle, $this->getSession(), $exactMatch);
        }

        if ($this->moduleWindow === null) {
            throw new ElementNotFoundException($this->getDriver());
        }

        return $this->moduleWindow;
    }

    /**
     * Helper method that returns the current editor window
     *
     * @param bool $exactMatch
     *
     * @return Window|null
     */
    protected function getEditorWindow($exactMatch = true)
    {
        // Cache the window reference as long as it is still valid
        if (!$this->editorWindow || !$this->editorWindow->isValid() || $exactMatch == false) {
            $this->editorWindow = Window::createFromTitle($this->editorWindowTitle, $this->getSession(), $exactMatch);
        }

        return $this->editorWindow;
    }

    /**
     * Clicks the selected icon for the entry with a given name.
     *
     * @param string $name
     * @param string $icon
     *
     * @throws \Exception
     */
    public function clickEntryIconByName($name, $icon)
    {
        $builder = new BackendXpathBuilder();

        $editIconXpath = $builder
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
