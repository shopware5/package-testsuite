<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class ShippingModule extends BackendModule
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Shipping';

    /**
     * Create a new shipping method from the details provided.
     *
     * @param array $shipping
     */
    public function createShippingMethodIfNotExists(array $shipping)
    {
        $this->open();

        if ($this->shippingMethodExists($shipping['name'])) {
            return;
        }

        $this->clickAddShippingMethodButton();
        $editor = $this->getEditorWindow();

        $configFormData = [
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
        $this->fillExtJsForm($editor, $configFormData);

        // Fill shipping cost cell
        $costCellXpath = BackendXpathBuilder::create()
            ->child('tr', ['~class' => 'x-grid-row'], 1)
            ->descendant('div', ['~class' => 'x-grid-cell-inner'], 3)
            ->getXpath();
        $costCell = $editor->find('xpath', $costCellXpath);
        $this->fillCell($costCell, $shipping['costs']);

        // Set optional shipping free limit
        if (array_key_exists('shippingfree', $shipping)) {
            $shippingFreeInputXpath = BackendXpathBuilder::getInputXpathByLabel('Versandkosten frei ab:');
            $shippingFreeInput = $editor->find('xpath', $shippingFreeInputXpath);
            $this->fillInput($shippingFreeInput, $shipping['shippingfree']);
        }

        // Activate payment methods if configured
        if (array_key_exists('activePaymentMethods', $shipping)) {
            $paymentMethods = strpos($shipping['activePaymentMethods'], ',')
                ? explode(', ', $shipping['activePaymentMethods'])
                : [$shipping['activePaymentMethods']];
            $this->activatePaymentMethods($editor, $paymentMethods);
        }

        // Activate countries if configured
        if (array_key_exists('activeCountries', $shipping)) {
            $countries = strpos($shipping['activeCountries'], ',')
                ? explode(', ', $shipping['activeCountries'])
                : [$shipping['activeCountries']];
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
        $deleteButtonXpath = BackendXpathBuilder::create()
            ->child('div', ['~text' => $shippingMethod])
            ->ancestor('tr', [], 1)
            ->descendant('img', ['~class' => 'sprite-minus-circle-frame'])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $deleteButtonXpath);
        $this->find('xpath', $deleteButtonXpath)->click();

        $confirmButton = $this->find('xpath',
            BackendXpathBuilder::create()->child('button', ['~text' => 'Ja'])->getXpath());
        $confirmButton->click();
    }

    /**
     * Set the shipping cost configuration for a given shipping method
     *
     * @param string $methodName
     * @param array $costData
     * @throws \Exception
     */
    public function setShippingCosts($methodName, array $costData)
    {
        $this->open();
        $window = $this->getModuleWindow();

        if (!$this->shippingMethodExists($methodName)) {
            throw new \Exception(sprintf('Missing shipping method "%s"', $methodName));
        }

        $methodRowXpath = BackendXpathBuilder::create()
            ->child('strong', ['@text' => $methodName])
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->getXpath();
        $methodRow = $window->find('xpath', $methodRowXpath);

        $editIcon = $methodRow->find('xpath', BackendXpathBuilder::getIconXpathByType('edit'));
        $editIcon->click();

        $editor = $this->getEditorWindow();

        // Empty out all previous cost configuration
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
     * Helper method to get shipping module window
     *
     * @return NodeElement|null
     */
    private function getModuleWindow()
    {
        $this->waitForText('Versandkosten Verwaltung');
        return $this->find('xpath', BackendXpathBuilder::getWindowXpathByTitle('Versandkosten Verwaltung'));
    }

    /**
     * Helper method that returns the shipping module editor window (if it is opened)
     *
     * @return NodeElement|null
     */
    private function getEditorWindow()
    {
        $editorXpath = BackendXpathBuilder::getWindowXpathByTitle('Versandkosten');
        $this->waitForSelectorPresent('xpath', $editorXpath);
        return $this->find('xpath', $editorXpath);
    }

    /**
     * Helper method that clicks the 'Add Shipping Method' button
     */
    private function clickAddShippingMethodButton()
    {
        $buttonXpath = '//button[@data-action="addShipping"]';
        $this->waitForSelectorPresent('xpath', $buttonXpath, 3);
        $this->find('xpath', $buttonXpath)->click();
    }

    /**
     * Helper method that returns true if a given shipping method already exists
     *
     * @param string $name
     * @return bool
     */
    private function shippingMethodExists($name)
    {
        $shippingMethod = $this->find('xpath', BackendXpathBuilder::create()
            ->child('strong', ['@text' => $name])
            ->getXpath());

        return null !== $shippingMethod;
    }

    /**
     * Activated a given set of payment methods for the shipping method that is currently being edited
     *
     * @param NodeElement $editor
     * @param array $paymentMethods
     */
    private function activatePaymentMethods(NodeElement $editor, array $paymentMethods)
    {
        $paymentTabXpath = BackendXpathBuilder::getTabXpathByLabel('Zahlart Auswahl');
        $editor->find('xpath', $paymentTabXpath)->click();

        foreach ($paymentMethods as $paymentMethod) {
            $row = $this->getGridRowByContent($paymentMethod, $editor);
            if (null !== $row) {
                $row->doubleClick();
            }
        }
    }

    /**
     * Activated a given set of countries for the shipping method that is currently being edited
     *
     * @param NodeElement $editor
     * @param array $countries
     */
    private function activateCountries(NodeElement $editor, array $countries)
    {
        $countriesTabXpath = BackendXpathBuilder::getTabXpathByLabel('Länder Auswahl');
        $editor->find('xpath', $countriesTabXpath)->click();

        foreach ($countries as $country) {
            $row = $this->getGridRowByContent($country, $editor);
            if (null !== $row) {
                $row->doubleClick();
            }
        }
    }

    /**
     * Helper method to get an extJs grid row by its content. Useful for assigning countries and payment methods
     * to a shipping method.
     *
     * @param string $text
     * @param NodeElement|null $scope
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
     * @param NodeElement $cell
     * @param string $columnValue
     */
    private function fillCell(NodeElement $cell, $columnValue)
    {
        $cell->doubleClick();
        $focusedInput = $this->waitForSelectorPresent('xpath', BackendXpathBuilder::getFocusedElementXpath());
        $this->fillInput($focusedInput, $columnValue);
    }
}