<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;

class ShippingModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Shipping';

    /**
     * @var string
     */
    protected $moduleWindowTitle = 'Versandkosten Verwaltung';

    /**
     * @var string
     */
    protected $editorWindowTitle = 'Versandkosten';

    /**
     * Create a new shipping method from the details provided.
     */
    public function createShippingMethodIfNotExists(array $shipping)
    {
        $this->open();

        if ($this->shippingMethodExists($shipping['name'])) {
            return;
        }

        $this->clickAddShippingMethodButton();
        $editor = $this->getEditorWindow();

        $configFormData = $this->buildShippingConfigFormData($shipping);
        $this->fillExtJsForm($editor, $configFormData);

        // Fill shipping cost cell
        $costCell = $this->getShippingCostCell($editor);
        $this->fillCell($costCell, $shipping['costs']);

        // Set optional shipping free limit
        if (\array_key_exists('shippingfree', $shipping)) {
            $editor->getInput('Versandkosten frei ab:')->setValue($shipping['shippingfree']);
        }

        // Activate payment methods if configured
        if (\array_key_exists('activePaymentMethods', $shipping)) {
            $paymentMethods = $this->extractPaymentMethodsToActivate($shipping);
            $this->activatePaymentMethods($editor, $paymentMethods);
        }

        // Activate countries if configured
        if (\array_key_exists('activeCountries', $shipping)) {
            $countries = $this->extractShippingCountriesToActivate($shipping);
            $this->activateCountries($editor, $countries);
        }

        $editor->findButton('Speichern')->click();
        $this->waitForText('Einstellungen wurden erfolgreich');
        $editor->findButton('Abbrechen')->click();
    }

    /**
     * Delete a shipping method by its name
     *
     * @param string $shippingMethod
     */
    public function deleteShippingMethod($shippingMethod)
    {
        $shippingRow = $this->getModuleWindow()->getGridView()->getRowByContent($shippingMethod);
        $shippingRow->clickActionIcon('sprite-minus-circle-frame');

        $confirmButtonXpath = BackendXpathBuilder::create()->child('button', ['@text' => 'Ja'])->getXpath();
        $this->waitForSelectorPresent('xpath', $confirmButtonXpath);
        $this->find('xpath', $confirmButtonXpath)->click();
    }

    /**
     * Set the shipping cost configuration for a given shipping method
     *
     * @param string $methodName
     *
     * @throws \Exception
     */
    public function setShippingCosts($methodName, array $costData)
    {
        $this->open();
        $window = $this->getModuleWindow();

        if (!$this->shippingMethodExists($methodName)) {
            throw new \Exception(sprintf('Missing shipping method "%s"', $methodName));
        }

        $methodRow = $window->getGridView()->getRowByContent($methodName);
        $methodRow->clickActionIcon('sprite-pencil');

        $editor = $this->getEditorWindow();

        // Empty out all previous cost configuration
        $this->emptyShippingCostConfiguration($editor);

        $columnMapping = [
            'from' => 1,
            'to' => 2,
            'costs' => 3,
            'factor' => 4,
        ];

        foreach ($costData as $rowIndex => $row) {
            foreach ($row as $columnKey => $columnValue) {
                $columnIndex = $columnMapping[$columnKey];

                $cellXPath = BackendXpathBuilder::create()
                    ->child('tr', ['~class' => 'x-grid-row'], $rowIndex + 1)
                    ->descendant('div', ['~class' => 'x-grid-cell-inner'], $columnIndex)
                    ->getXpath();

                $cell = $editor->find('xpath', $cellXPath);
                $this->fillCell($cell, $columnValue);
            }
        }
    }

    /**
     * Helper method that clicks the 'Add Shipping Method' button
     */
    private function clickAddShippingMethodButton()
    {
        $buttonXpath = '//button[@data-action="addShipping"]';
        $this->waitForSelectorPresent('xpath', $buttonXpath, 7);
        $this->find('xpath', $buttonXpath)->click();
    }

    /**
     * Helper method that returns true if a given shipping method already exists
     *
     * @param string $name
     *
     * @return bool
     */
    private function shippingMethodExists($name)
    {
        $shippingMethod = $this->find('xpath', BackendXpathBuilder::create()
            ->child('strong', ['@text' => $name])
            ->getXpath());

        return $shippingMethod !== null;
    }

    /**
     * Activated a given set of payment methods for the shipping method that is currently being edited
     */
    private function activatePaymentMethods(NodeElement $editor, array $paymentMethods)
    {
        $paymentTabXpath = BackendXpathBuilder::getTabXpathByLabel('Zahlart Auswahl');
        $editor->find('xpath', $paymentTabXpath)->click();

        foreach ($paymentMethods as $paymentMethod) {
            $row = $this->getGridRowByContent($paymentMethod, $editor);
            if ($row !== null) {
                $row->doubleClick();
            }
        }
    }

    /**
     * Activated a given set of countries for the shipping method that is currently being edited
     */
    private function activateCountries(NodeElement $editor, array $countries)
    {
        $countriesTabXpath = BackendXpathBuilder::getTabXpathByLabel('Länder Auswahl');
        $editor->find('xpath', $countriesTabXpath)->click();

        foreach ($countries as $country) {
            $row = $this->getGridRowByContent($country, $editor);
            if ($row !== null) {
                $row->doubleClick();
            }
        }
    }

    /**
     * Helper method to get an extJs grid row by its content. Useful for assigning countries and payment methods
     * to a shipping method.
     *
     * @param string $text
     *
     * @return NodeElement|null
     */
    private function getGridRowByContent($text, NodeElement $scope = null)
    {
        $xpath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $text])
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->getXpath();

        $scope = $scope ?: $this;

        return $scope->find('xpath', $xpath);
    }

    /**
     * Helper method that fills a given cell with a value
     *
     * @param string $columnValue
     */
    private function fillCell(NodeElement $cell, $columnValue)
    {
        $cell->doubleClick();
        $focusedInput = $this->waitForSelectorPresent('xpath', BackendXpathBuilder::getFocusedElementXpath());
        $focusedInput->setValue($columnValue);
    }

    /**
     * @return array
     */
    private function buildShippingConfigFormData(array $shipping)
    {
        return [
            ['label' => 'Name:', 'value' => $shipping['name'], 'type' => 'input'],
            [
                'label' => 'Versandkosten-Berechnung nach:',
                'value' => $shipping['calculationType'],
                'type' => 'combobox',
            ],
            ['label' => 'Versandart-Typ:', 'value' => $shipping['shippingType'], 'type' => 'combobox'],
            ['label' => 'Zahlungsart-Aufschlag:', 'value' => $shipping['surchargeCalculation'], 'type' => 'combobox'],
            ['label' => 'Aktiv:', 'value' => true, 'type' => 'checkbox'],
        ];
    }

    /**
     * @return NodeElement
     */
    private function getShippingCostCell(NodeElement $editor)
    {
        $costCellXpath = BackendXpathBuilder::create()
            ->child('tr', ['~class' => 'x-grid-row'], 1)
            ->descendant('div', ['~class' => 'x-grid-cell-inner'], 3)
            ->getXpath();
        $costCell = $editor->find('xpath', $costCellXpath);

        return $costCell;
    }

    /**
     * @return array
     */
    private function extractPaymentMethodsToActivate(array $shipping)
    {
        $paymentMethods = strpos($shipping['activePaymentMethods'], ',')
            ? explode(', ', $shipping['activePaymentMethods'])
            : [$shipping['activePaymentMethods']];

        return $paymentMethods;
    }

    /**
     * @return array
     */
    private function extractShippingCountriesToActivate(array $shipping)
    {
        $countries = strpos($shipping['activeCountries'], ',')
            ? explode(', ', $shipping['activeCountries'])
            : [$shipping['activeCountries']];

        return $countries;
    }

    private function emptyShippingCostConfiguration(Window $editor)
    {
        $costRowsXpath = BackendXpathBuilder::create()->child('tr', ['~class' => 'x-grid-row'])->getXpath();
        $costRows = $editor->findAll('xpath', $costRowsXpath);
        array_shift($costRows);

        /** @var NodeElement $costRow */
        foreach ($costRows as $costRow) {
            $deleteIcon = $costRow->find('xpath', BackendXpathBuilder::getIconXpathByType('delete'));
            $deleteIcon->click();

            $messageBoxXpath = BackendXpathBuilder::getWindowXpathByTitle('Den ausgewählten Eintrag löschen?');
            $this->waitForSelectorPresent('xpath', $messageBoxXpath);
            $messageBox = $this->find('xpath', $messageBoxXpath);
            $messageBox->findButton('Ja')->click();
        }
    }
}
