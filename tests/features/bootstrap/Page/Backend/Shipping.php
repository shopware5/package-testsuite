<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Shipping extends ContextAwarePage implements HelperSelectorInterface
{
    private $requiredFields = ['Versandkosten-Berechnung nach', 'Versandart-Typ', 'Zahlungsart-Aufschlag'];

    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Shipping';

    /**
     * @var array
     */
    private $xPaths;

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        if (count($this->xPaths) == 0) {
            $xp = new XpathBuilder();
            $this->xPaths = [
                'window' => $xp
                    ->xWindowByTitle('Versandkosten Verwaltung')
                    ->get(),
                'windowClose' => $xp
                    ->img('desc', ['~class' => 'x-tool-close'], 1)
                    ->get(),
                'addShippingButton' => $xp
                    ->div('desc', ['~class' => 'x-toolbar'])
                    ->span('desc', ['~class' => 'x-btn-inner', 'and', '~text' => 'Hinzufügen'])
                    ->button('asc', [], 1)
                    ->get(),
                'shippingTableHeaders' => $xp
                    ->div('desc', ['~class' => 'x-grid-header-ct'])
                    ->span('desc', ['~class' => 'x-column-header-text'])
                    ->get(),
                'shippingTableRows' => $xp
                    ->table('desc', ['~class' => 'x-grid-table'])
                    ->tr('desc', ['~class' => 'x-grid-row'])
                    ->get(),
                'shippingTableRowCells' => $xp
                    ->td('desc', ['~class' => 'x-grid-cell'])
                    ->get(),
                'shippingTableRowEditButton' => $xp
                    ->img('desc', ['@data-qtip' => 'Bearbeiten Sie diese Versandkosten'])
                    ->get(),
                'editorWindow' => $xp
                    ->xWindowByExactTitle('Versandkosten')
                    ->get(),
                'shippingCostCell' => $xp
                    ->tr('desc', ['~class' => 'x-grid-row'], 1)
                    ->div('desc', ['~class' => 'x-grid-cell-inner'], 3)
                    ->get(),
                'editFormNameField' => $xp->getXInputForLabel('Name:'),
                'editFormDescriptionField' => $xp->getXTextareaForLabel('Beschreibung:'),
                'editFormActiveButton' => $xp->getXInputForLabel('Aktiv:'),
                'editFormCalculationSelectorPebble' => $xp->getXSelectorPebbleForLabel('Versandkosten-Berechnung nach:'),
                'editFormTypeSelectorPebble' => $xp->getXSelectorPebbleForLabel('Versandart-Typ:'),
                'editFormSurchargeSelectorPebble' => $xp->getXSelectorPebbleForLabel('Zahlungsart-Aufschlag:'),
                'editFormActiveCheckbox' => $xp->getXInputForLabel('Aktiv:'),
            ];
        }
        return $this->xPaths;
    }

    public function createShippingMethodIfNotExists(array $shipping)
    {
        $xp = new XpathBuilder();

        $xPaths = $this->getXPathSelectors();
        $this->open();

        $this->waitForText('Versandkosten Verwaltung');

        $window = $this->find('xpath', $xPaths['window']);

        $existingName = $this->find('xpath', $xp->strong(['@text' => $shipping['name']])->div('asc', ['~class' => 'x-grid-cell-inner'])->get());
        if ($existingName != null) {
            return;
        }

        /** @var NodeElement $addButton */
        $addButton = $window->find('xpath', $xPaths['addShippingButton']);
        $addButton->click();

        $this->waitForText('Tracking-URL');

        $editor = $this->waitForSelectorPresent('xpath', $xPaths['editorWindow']);
        $this->assertNotNull($editor, print_r($xPaths['editorWindow'], true));

        $editor->find('xpath', $xp->getXFormElementForLabel('Name:', 'input'))->setValue($shipping['name']);

        $this->setBackendDropdownValue($editor, $xPaths['editFormCalculationSelectorPebble'], 'calculation', $shipping['calculationType']);

        $windowNames = $this->getSession()->getWindowNames();
        while(count($windowNames) > 1) {
            $this->getSession()->switchToWindow(end($windowNames));
            $this->getSession()->executeScript('window.close()');
            usleep(200);
            $windowNames = $this->getSession()->getWindowNames();
        }
        $this->getSession()->switchToWindow(end($windowNames));

        //$this->waitForClassNotPresent($editor, $xPaths['editFormTypeSelectorPebble'], 'x-unselectable');
        $this->setBackendDropdownValue($editor, $xPaths['editFormTypeSelectorPebble'], 'type', $shipping['shippingType']);
        $this->setBackendDropdownValue($editor, $xPaths['editFormSurchargeSelectorPebble'], 'surchargeCalculation', $shipping['surchargeCalculation']);

        /** @var NodeElement $shippingCostCell */
        $shippingCostCell = $editor->find('xpath', $xPaths['shippingCostCell']);
        $this->assertNotNull($shippingCostCell, print_r($xPaths['shippingCostCell'], true));
        $shippingCostCell->doubleClick();

        /** @var NodeElement $focussedInput */
        $focussedInput = $this->waitForSelectorPresent('xpath', $xp->getXFocussedInput());
        $this->assertNotNull($focussedInput, print_r($xp->getXFocussedInput(), true));
        $focussedInput->setValue($shipping['costs']);

        $editor->find('xpath', $xPaths['editFormActiveCheckbox'])->click();

        if (array_key_exists('shippingfree', $shipping)) {
            $editor->find('xpath', $xp->getXFormElementForLabel('Versandkosten frei ab:', 'input'))->setValue($shipping['shippingfree']);
        }

        $this->saveEditorAndClose($editor, 'Tracking-URL');
    }

    public function setShippingCosts($method, $data)
    {
        $xp = new XpathBuilder();

        $xPaths = $this->getXPathSelectors();
        $this->open();

        $this->waitForText('Versandkosten Verwaltung');

        $window = $this->find('xpath', $xPaths['window']);

        $methodRow = $window->find('xpath', $xp->strong(['@text' => $method])->tr('asc', ['~class' => 'x-grid-row'])->get());

        if ($methodRow == null) {
            throw new \Exception(sprintf('Missing shipping method "%s"', $method));
        }

        $editIcon = $methodRow->find('xpath', $xp->getXPencilIcon());
        $this->assertNotNull($editIcon, print_r($xp->getXPencilIcon(), true));
        $editIcon->click();

        $this->waitForText('Tracking-URL');

        $editor = $this->find('xpath', $xPaths['editorWindow']);

        $rowsXPath = $xp->tr('desc', ['~class' => 'x-grid-row'])->get();
        $rows = $editor->findAll('xpath', $rowsXPath);
        array_shift($rows);

        foreach ($rows as $row) {
            $deleteIcon = $row->find('xpath', $xp->getXMinusIcon());
            $this->assertNotNull($deleteIcon, print_r($xp->getXMinusIcon(), true));
            $deleteIcon->click();

            $messageBox = $this->getSession()->getPage()->find('xpath',
                "//span[text()='Den ausgewählten Eintrag löschen?']/ancestor::div[" . XpathBuilder::getContainsClassString('x-message-box') . "][1]");

            $messageBox->find('xpath', "/descendant::span[text()='Ja']")->click();
        }

        $columnMapping = [
            'from' => 1,
            'to' => 2,
            'costs' => 3,
            'factor' => 4
        ];

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnKey => $columnValue) {
                $columnIndex = $columnMapping[$columnKey];

                $cellXPath = $xp
                    ->tr('desc', ['~class' => 'x-grid-row'], $rowIndex + 1)
                    ->div('desc', ['~class' => 'x-grid-cell-inner'], $columnIndex)
                    ->get();

                $cell = $editor->find('xpath', $cellXPath);
                $this->assertNotNull($cell, print_r($cellXPath, true));
                $cell->doubleClick();

                /** @var NodeElement $focussedInput */
                $focussedInput = $this->waitForSelectorPresent('xpath', $xp->getXFocussedInput());
                $this->assertNotNull($focussedInput, print_r($xp->getXFocussedInput(), true));
                $focussedInput->setValue($columnValue);

                $editor->find('xpath', $xPaths['editFormActiveCheckbox'])->click();
            }
        }
    }

    public function activatePaymentMethodsForShippingMethod($method, $data)
    {
        $data = Helper::flattenArray($data);

        $xp = new XpathBuilder();

        $xPaths = $this->getXPathSelectors();
        $this->open();

        $this->waitForText('Versandkosten Verwaltung');

        $window = $this->find('xpath', $xPaths['window']);

        $methodRow = $window->find('xpath', $xp->strong(['@text' => $method])->tr('asc', ['~class' => 'x-grid-row'])->get());

        if ($methodRow == null) {
            throw new \Exception(sprintf('Missing shipping method "%s"', $method));
        }

        $editIcon = $methodRow->find('xpath', $xp->getXPencilIcon());
        $this->assertNotNull($editIcon, print_r($xp->getXPencilIcon(), true));
        $editIcon->click();

        $this->waitForText('Tracking-URL');

        $editor = $this->find('xpath', $xPaths['editorWindow']);

        /** @var NodeElement $paymentMethodsTab */
        $paymentMethodsTab = $editor->find('xpath', $xp->getXTabContainerForLabel('Zahlart Auswahl'));
        $paymentMethodsTab->click();

        $availablePaymentMethodRowsXPath = $xp->getXGridBodyForLabel('Verfügbar') . $xp->tr('desc', ['~class' => 'x-grid-row'])->get();

        $addButton = $this->waitForSelectorPresent('xpath', $xp->button('desc', ['@data-qtip' => 'Add to Selected'])->get());

        foreach ($data as $payment) {
            /** @var NodeElement[] $availablePaymentMethodRows */
            $availablePaymentMethodRows = $editor->findAll('xpath', $availablePaymentMethodRowsXPath);

            foreach ($availablePaymentMethodRows as $row) {
                $text = trim($row->getText());
                if ($text != trim($payment)) {
                    continue;
                }

                $paymentRowXpath = $xp->div(['@text' => $text, 'and', '~class' => 'x-grid-cell-inner'])->tr('asc', [], 1)->get();
                $paymentRow = $this->waitForSelectorPresent('xpath', $paymentRowXpath);

                $this->assertNotNull($paymentRow, print_r($row->getXpath(), true));
                $paymentRow->click();
                $addButton->click();
                break;
            }
        }

        $this->saveEditorAndClose($editor, 'Tracking-URL');
    }

    public function activateCountriesForShippingMethod($method, $data)
    {
        $data = Helper::flattenArray($data);

        $xp = new XpathBuilder();

        $xPaths = $this->getXPathSelectors();
        $this->open();

        $this->waitForText('Versandkosten Verwaltung');

        $window = $this->find('xpath', $xPaths['window']);

        $methodRow = $window->find('xpath', $xp->strong(['@text' => $method])->tr('asc', ['~class' => 'x-grid-row'])->get());
        if ($methodRow == null) {
            throw new \Exception(sprintf('Missing shipping method "%s"', $method));
        }

        $editIcon = $methodRow->find('xpath', $xp->getXPencilIcon());
        $editIcon->click();

        $this->waitForText('Tracking-URL');

        $editor = $this->find('xpath', $xPaths['editorWindow']);

        /** @var NodeElement $countriesTab */
        $countriesTab = $editor->find('xpath', $xp->getXTabContainerForLabel('Länder Auswahl'));
        $countriesTab->click();

        $availableCountriesRowsXPath = $xp->getXGridBodyForLabel('Verfügbar') . $xp->tr('desc', ['~class' => 'x-grid-row'])->get();
        $addButton = $this->waitForSelectorPresent('xpath', $xp->button('desc', ['@data-qtip' => 'Add to Selected'])->get());

        foreach ($data as $payment) {
            /** @var NodeElement[] $availableCountriesRows */
            $availableCountriesRows = $editor->findAll('xpath', $availableCountriesRowsXPath);

            foreach ($availableCountriesRows as $row) {
                $text = trim($row->getText());
                if ($text != trim($payment)) {
                    continue;
                }
                $paymentRowXpath = $xp->div(['@text' => $text, 'and', '~class' => 'x-grid-cell-inner'])->tr('asc', [], 1)->get();
                $countryRow = $this->waitForSelectorPresent('xpath', $paymentRowXpath);

                $this->assertNotNull($countryRow, print_r($row->getXpath(), true));
                $countryRow->click();
                $addButton->click();
                break;
            }
        }

        $this->saveEditorAndClose($editor, 'Tracking-URL');
    }

    /**
     * @param NodeElement $editor
     * @param string $editorIdentifier Unique string which dissapperance is used to recognize the successfull closing
     */
    private function saveEditorAndClose(NodeElement $editor, $editorIdentifier)
    {
        $xp = new XpathBuilder();
        /** @var NodeElement $saveButton */
        $saveButton = $editor->find('xpath', $xp->span(['@text' => 'Speichern'])->get());
        $saveButton->click();
        $this->waitForText('Einstellungen wurden erfolgreich');
        /** @var NodeElement $cancelButton */
        $cancelButton = $editor->find('xpath', $xp->span(['@text' => 'Abbrechen'])->get());
        $cancelButton->click();
        $this->waitForTextNotPresent($editorIdentifier);
    }
}
