<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\Window;

class CustomerModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Customer';

    /**
     * @var string
     */
    protected $moduleWindowTitle = 'Kunden';

    /**
     * Helper function that skips the intro wizard of the customer module
     */
    public function skipIntroWizardIfNecessary()
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
    public function fillNewCustomerFormWith(array $data)
    {
        $window = $this->getNewCustomerWindow();
        $this->fillCustomerForm($window, $data);
    }

    /**
     * Fill the edit customer form with the supplied data
     */
    public function fillEditCustomerFormWith(array $data)
    {
        $window = $this->getEditCustomerWindow();
        $this->fillCustomerForm($window, $data);
    }

    /**
     * Click the edit icon for the customer with a given
     * firstname.
     */
    public function openEditFormForCustomer($firstname)
    {
        $window = $this->getModuleWindow();
        $customerRow = $window->getGridView()->getRowByContent($firstname);
        $customerRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Click the delete icon for the customer with a given
     * firstname.
     */
    public function clickDeleteIconForCustomer($firstname)
    {
        $window = $this->getModuleWindow();
        $customerRow = $window->getGridView()->getRowByContent($firstname);
        $customerRow->clickActionIcon('sprite-minus-circle-frame');
    }

    /**
     * Fill a form within the supplied window with the supplied form data
     */
    private function fillCustomerForm(Window $window, array $data)
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
     *
     * @param string $value
     */
    private function fillPaymentCombobox(NodeElement $combobox, $value)
    {
        $builder = new BackendXpathBuilder();

        $pebble = $combobox->find('xpath', $builder->child('div', ['~class' => 'x-form-trigger'])->getXpath());
        $pebble->click();

        sleep(1);

        $options = $this->findAll('xpath',
            $builder->reset()->child('div', ['~class' => 'x-boundlist-item', 'and', '@text' => $value])->getXpath());
        /** @var NodeElement $option */
        foreach ($options as $option) {
            try {
                $option->click();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Helper method to get the "new customer" window node element
     *
     * @return Window
     */
    private function getNewCustomerWindow()
    {
        return Window::createFromTitle('Kunden-Administration - Neuen Kunden erstellen', $this->getSession());
    }

    /**
     * Helper method to get the "edit customer" window node element
     *
     * @return Window
     */
    private function getEditCustomerWindow()
    {
        return Window::createFromTitle('Kundenkonto:', $this->getSession(), false);
    }
}
