<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Exception;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;

class CustomerModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Customer';

    protected string $moduleWindowTitle = 'Kunden';

    /**
     * Helper function that skips the intro wizard of the customer module
     */
    public function skipIntroWizardIfNecessary(): void
    {
        if ($this->waitIfThereIsText('Überspringen')) {
            $skipButton = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Überspringen'));
            $skipButton->click();
        }
    }

    /**
     * Fill the new customer form of the backend module with
     * the supplied data.
     */
    public function fillNewCustomerFormWith(array $data): void
    {
        $window = $this->getNewCustomerWindow();
        $this->fillCustomerForm($window, $data);
    }

    /**
     * Fill the edit customer form with the supplied data
     */
    public function fillEditCustomerFormWith(array $data): void
    {
        $window = $this->getEditCustomerWindow();
        $this->fillCustomerForm($window, $data);
    }

    /**
     * Click the edit icon for the customer with a given
     * firstname.
     */
    public function openEditFormForCustomer(string $firstname): void
    {
        $customerRow = $this->getModuleWindow()->getGridView()->getRowByContent($firstname);
        $customerRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Click the delete icon for the customer with a given
     * firstname.
     */
    public function clickDeleteIconForCustomer(string $firstname): void
    {
        $customerRow = $this->getModuleWindow()->getGridView()->getRowByContent($firstname);
        $customerRow->clickActionIcon('sprite-minus-circle-frame');
    }

    /**
     * Fill a form within the supplied window with the supplied form data
     */
    private function fillCustomerForm(Window $window, array $data): void
    {
        // Fill most form elements
        $this->fillExtJsForm($window, $data);

        $paymentFields = array_filter($data, function ($row) {
            return $row['type'] === 'paymentbox';
        });

        foreach ($paymentFields as $row) {
            $combobox = $window->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel($row['label']));
            $this->fillPaymentCombobox($combobox, $row['value']);
        }
    }

    /**
     * Special helper method that fills an extJS payment info combobox
     */
    private function fillPaymentCombobox(NodeElement $combobox, string $value): void
    {
        $builder = new BackendXpathBuilder();

        $pebble = $combobox->find('xpath', $builder->child('div', ['~class' => 'x-form-trigger'])->getXpath());
        $pebble->click();

        sleep(1);

        $options = $this->findAll('xpath',
            $builder->reset()->child('div', ['~class' => 'x-boundlist-item', 'and', '@text' => $value])->getXpath());

        foreach ($options as $option) {
            try {
                $option->click();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Helper method to get the "new customer" window node element
     */
    private function getNewCustomerWindow(): Window
    {
        return Window::createFromTitle('Kunden-Administration - Neuen Kunden erstellen', $this->getSession());
    }

    /**
     * Helper method to get the "edit customer" window node element
     */
    private function getEditCustomerWindow(): Window
    {
        return Window::createFromTitle('Kundenkonto:', $this->getSession(), false);
    }
}
