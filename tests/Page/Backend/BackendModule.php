<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;
use Shopware\Page\ContextAwarePage;

class BackendModule extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $moduleWindowTitle = '';

    /**
     * @var string
     */
    protected $editorWindowTitle = '';

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
     * @param NodeElement $formParent
     * @param array $formElements
     * @throws \Exception
     */
    public function fillExtJsForm(NodeElement $formParent, array $formElements)
    {
        foreach ($formElements as $element) {

            // Change scope to fieldset if specified
            $parent = isset($element['fieldset'])
                ? $formParent->find('xpath', BackendXpathBuilder::getFieldsetXpathByLabel($element['fieldset']))
                : $formParent;

            switch ($element['type']) {
                case 'input':
                    $input = $parent->find('xpath', BackendXpathBuilder::getInputXpathByLabel($element['label']));
                    if (!$input) {
                        throw new \Exception('Could not find input element by label ' . $element['label']);
                    }
                    $this->fillInput($input, $element['value']);
                    break;
                case 'combobox':
                    $combobox = $parent->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel($element['label']));
                    $this->fillCombobox($combobox, $element['value']);
                    break;
                case 'checkbox':
                    $checkbox = $parent->find('xpath', BackendXpathBuilder::getInputXpathByLabel($element['label']));
                    $this->toggleCheckbox($checkbox);
                    break;
                case 'textarea':
                    $textarea = $parent->find('xpath',
                        BackendXpathBuilder::getFormElementXpathByLabel($element['label'], 'textarea'));
                    $this->fillInput($textarea, $element['value']);
                    break;
                case 'selecttree':
                    $selecttree = $parent->find('xpath',
                        BackendXpathBuilder::getSelectorPebbleXpathByLabel($element['label']));
                    $this->fillSelecttree($selecttree, $element['value']);
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
     * Fills in a checkbox
     *
     * @param NodeElement $checkbox
     */
    public function toggleCheckbox(NodeElement $checkbox)
    {
        $checkbox->click();
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
            throw new \RuntimeException('Pebble with for combobox with value ' . $value . 'not visible.');
        }

        $pebble->click();

        $dropdownsXpath = BackendXpathBuilder::create()->child('div', ['~class' => 'x-boundlist'])->getXpath();
        $this->waitForSelectorPresent('xpath', $dropdownsXpath, 5);

        $dropdowns = $this->findAll('xpath', $dropdownsXpath);
        /** @var NodeElement $dropdown */
        foreach ($dropdowns as $dropdown) {
            if ($this->elementsTouch($dropdown, $pebble)) {
                $optionXpath = BackendXpathBuilder::create()
                    ->child('li', ['@role' => 'option', 'and', '@text' => $value])
                    ->getXpath();

                $option = $dropdown->find('xpath', $optionXpath);
                $option->click();
                break;
            }
        }

        sleep(1);
    }

    /**
     * Helper method that fills an extJS combobox input field
     *
     * @param NodeElement $comboInputField
     * @param string $value
     */
    public function fillComboboxInput(NodeElement $comboInputField, $value)
    {
        $comboInputField->click();
        $comboInputField->setValue($value);
    }

    /**
     * Selects an entry in a selecttree
     *
     * @param NodeElement $selecttree
     * @param string $value Data which should be used for the selection
     * @throws \Exception
     */
    public function fillSelecttree($selecttree, $value)
    {
        $selecttree->click();
        $elementXpaths = BackendXpathBuilder::getSelectTreeElementXpaths($value);

        foreach ($elementXpaths as $xpath) {
            $dropdownXpath = BackendXpathBuilder::create()
                ->descendant('div', ['@text' => 'Deutsch'])
                ->ancestor('div', ['~class' => 'x-tree-panel'])
                ->getXpath();
            $this->waitForXpathElementPresent($dropdownXpath);

            $dropdown = $this->find('xpath', $dropdownXpath);
            $option = $dropdown->find('xpath', $xpath);
            $option->click();
        }
    }

    /**
     * Expands a collapsed element
     *
     * @param string $label
     * @param null $fieldset
     */
    public function expandCategoryCollapsible($label, $fieldset = null)
    {
        $builder = new BackendXpathBuilder();

        $collapsibleFieldXpath = $builder
            ->child('div', ['@text' => $label], 1)
            ->ancestor('tr', [], 1)
            ->getXpath();

        if ($fieldset) {
            /** @var NodeElement $fieldset */
            $element = $fieldset->find('xpath', $collapsibleFieldXpath);
        } else {
            $element = $this->find('xpath', $collapsibleFieldXpath);
        }

        $element->doubleClick();
    }

    /**
     * Chooses the desired answer in a message box
     *
     * @param $answer
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


    /**
     * Helper method that returns the current module window
     *
     * @param bool $exactMatch
     * @return Window|null
     */
    protected function getModuleWindow($exactMatch = true)
    {
        return new Window($this->moduleWindowTitle, $this->getSession(), $exactMatch);
    }

    /**
     * Helper method that returns the current editor window
     *
     * @param bool $exactMatch
     * @return Window|null
     */
    protected function getEditorWindow($exactMatch = true)
    {
        return new Window($this->editorWindowTitle, $this->getSession(), $exactMatch);
    }

    /**
     * Helper method that returns true if two NodeElements touch
     *
     * @param NodeElement $elemA
     * @param NodeElement $elemB
     * @return bool
     * @throws \Behat\Mink\Exception\DriverException
     */
    private function elementsTouch(NodeElement $elemA, NodeElement $elemB)
    {
        $idA = $elemA->getAttribute('id');
        $aTop = $this->getYCoordinateForElement($idA, 'top');
        $aBottom = $this->getYCoordinateForElement($idA, 'bottom');

        $idB = $elemB->getAttribute('id');
        $bTop = $this->getYCoordinateForElement($idB, 'top');
        $bBottom = $this->getYCoordinateForElement($idB, 'bottom');

        return abs($aTop - $bBottom) < 5 || abs($aBottom - $bTop) < 5;
    }

    /**
     * Get the bounding box position value for any element on the page by it's id
     *
     * @param string $id
     * @param string $side Can be either top, bottom, left or right
     * @return int
     * @throws \Behat\Mink\Exception\DriverException
     */
    private function getYCoordinateForElement($id, $side = 'top')
    {
        return (int)$this->getSession()->getDriver()->evaluateScript(
            "return document.getElementById('" . $id . "').getBoundingClientRect()." . $side . ";"
        );
    }

    /**
     * Clicks the selected icon for the entry with a given name.
     *
     * @param $name
     * @param $icon
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
