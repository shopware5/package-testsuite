<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;

class ShippingModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Shipping';

    protected string $moduleWindowTitle = 'Versandkosten Verwaltung';

    protected string $editorWindowTitle = 'Versandkosten';

    /**
     * Create a new shipping method from the details provided.
     */
    public function createShippingMethodIfNotExists(array $shipping): void
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
     */
    public function deleteShippingMethod(string $shippingMethod): void
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
     * @throws Exception
     */
    public function setShippingCosts(string $methodName, array $costData): void
    {
        $this->open();
        $window = $this->getModuleWindow();

        if (!$this->shippingMethodExists($methodName)) {
            throw new Exception(\sprintf('Missing shipping method "%s"', $methodName));
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
    private function clickAddShippingMethodButton(): void
    {
        $buttonXpath = '//button[@data-action="addShipping"]';
        $this->waitForSelectorPresent('xpath', $buttonXpath, 7);
        $this->find('xpath', $buttonXpath)->click();
    }

    /**
     * Helper method that returns true if a given shipping method already exists
     */
    private function shippingMethodExists(string $name): bool
    {
        try {
            $this->find('xpath', BackendXpathBuilder::create()->child('strong', ['@text' => $name])->getXpath());
        } catch (ElementNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Activated a given set of payment methods for the shipping method that is currently being edited
     */
    private function activatePaymentMethods(NodeElement $editor, array $paymentMethods): void
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
    private function activateCountries(NodeElement $editor, array $countries): void
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
     */
    private function getGridRowByContent(string $text, ?NodeElement $scope = null): ?NodeElement
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
     */
    private function fillCell(NodeElement $cell, string $columnValue): void
    {
        $cell->doubleClick();
        $focusedInput = $this->waitForSelectorPresent('xpath', BackendXpathBuilder::getFocusedElementXpath());
        $focusedInput->setValue($columnValue);
    }

    private function buildShippingConfigFormData(array $shipping): array
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

    private function getShippingCostCell(NodeElement $editor): NodeElement
    {
        $costCellXpath = BackendXpathBuilder::create()
            ->child('tr', ['~class' => 'x-grid-row'], 1)
            ->descendant('div', ['~class' => 'x-grid-cell-inner'], 3)
            ->getXpath();

        return $editor->find('xpath', $costCellXpath);
    }

    private function extractPaymentMethodsToActivate(array $shipping): array
    {
        return strpos($shipping['activePaymentMethods'], ',')
            ? explode(', ', $shipping['activePaymentMethods'])
            : [$shipping['activePaymentMethods']];
    }

    private function extractShippingCountriesToActivate(array $shipping): array
    {
        return strpos($shipping['activeCountries'], ',')
            ? explode(', ', $shipping['activeCountries'])
            : [$shipping['activeCountries']];
    }

    private function emptyShippingCostConfiguration(Window $editor): void
    {
        $costRowsXpath = BackendXpathBuilder::create()->child('tr', ['~class' => 'x-grid-row'])->getXpath();
        $costRows = $editor->findAll('xpath', $costRowsXpath);
        array_shift($costRows);

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
