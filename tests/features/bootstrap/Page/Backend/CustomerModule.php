<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class CustomerModule extends BackendModule
{
    /**
     * Fill the new customer form of the backend module with
     * the supplied data.
     *
     * @param array $data
     */
    public function fillNewCustomerFormWith(array $data)
    {
        $window = $this->getNewCustomerWindow();

        $this->fillCustomerForm($window, $data);
    }

    /**
     * Fill the edit customer form with the supplied data
     *
     * @param array $data
     */
    public function fillEditCustomerFormWith(array $data)
    {
        $window = $this->getEditCustomerWindow();

        $this->fillCustomerForm($window, $data);
    }

    /**
     * Click the edit icon for the customer with a given
     * firstname.
     *
     * @param $firstname
     */
    public function openEditFormForCustomer($firstname)
    {
        $builder = new BackendXpathBuilder();

        $editIconXpath = $builder
            ->child('div')
            ->contains($firstname)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => 'sprite-pencil'])
            ->getXpath();

        $this->waitForXpathElementPresent($editIconXpath);
        $editIcon = $this->find('xpath', $editIconXpath);
        $editIcon->click();
    }

    /**
     * Click the delete icon for the customer with a given
     * firstname.
     *
     * @param $firstname
     */
    public function clickDeleteIconForCustomer($firstname)
    {
        $builder = new BackendXpathBuilder();

        $deleteIconXpath = $builder
            ->child('div')
            ->contains($firstname)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => 'sprite-minus-circle-frame'])
            ->getXpath();

        $this->waitForXpathElementPresent($deleteIconXpath);
        $deleteIcon = $this->find('xpath', $deleteIconXpath);
        $deleteIcon->click();
    }

    /**
     * Fill a form within the supplied window with the supplied form data
     *
     * @param NodeElement $window
     * @param array $data
     */
    private function fillCustomerForm(NodeElement $window, array $data)
    {
        foreach ($data as $entry) {
            /** @var NodeElement $parent */
            $parent = $window->find('xpath', (new BackendXpathBuilder())->getFieldsetXpathByLabel($entry['fieldset']));

            if(!$parent) {
                throw new \Exception('Could not find fieldset with name ' . $entry['fieldset']);
            }

            switch ($entry['type']) {
                case 'input':
                    $input = $parent->find('xpath', (new BackendXpathBuilder())->getInputXpathByLabel($entry['label']));

                    if(!$input) {
                        throw new \Exception('Could not find input with label ' . $entry['label']);
                    }

                    $this->fillInput($input, $entry['value']);
                    break;
                case 'combobox':
                    $combobox = $parent->find('xpath', (new BackendXpathBuilder())->getComboboxXpathByLabel($entry['label']));

                    if(!$combobox) {
                        throw new \Exception('Could not find input with label ' . $entry['label']);
                    }

                    $this->fillCombobox($combobox, $entry['value']);
                    break;
                case 'paymentbox':
                    $combobox = $parent->find('xpath', (new BackendXpathBuilder())->getComboboxXpathByLabel($entry['label']));

                    if(!$combobox) {
                        throw new \Exception('Could not find input with label ' . $entry['label']);
                    }

                    $this->fillPaymentCombobox($combobox, $entry['value']);
                    break;
            }
        }
    }

    /**
     * Special helper method that fills an extJS payment info combobox
     *
     * @param NodeElement $parent
     */
    private function fillPaymentCombobox(NodeElement $combobox, $value)
    {
        $builder = new BackendXpathBuilder();

        $pebble = $combobox->find('xpath', $builder->child('div', ['~class' => 'x-form-trigger'])->getXpath());
        $pebble->click();

        sleep(1);

        // Please refer to comment in BackendModule::fillCombobox() for more details to why this solution is necessary
        $options = $this->findAll('xpath', $builder->reset()->child('div', ['~class' => 'x-boundlist-item', 'and', '@text' => $value])->getXpath());
        /** @var NodeElement $option */
        foreach($options as $option) {
            try {
                $option->click();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Helper method to get the "new customer" window node element
     * @return NodeElement
     * @throws \Exception
     */
    private function getNewCustomerWindow()
    {
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle('Kunden-Administration - Neuen Kunden erstellen');

        $window = $this->find('xpath', $windowXpath);

        if(!$window) {
            throw new \Exception('Could not find customer module.');
        }

        return $window;
    }

    /**
     * Helper method to get the "edit customer" window node element
     *
     * @return NodeElement
     * @throws \Exception
     */
    private function getEditCustomerWindow()
    {
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle('Kundenkonto:', false);

        $window = $this->find('xpath', $windowXpath);

        if(!$window) {
            throw new \Exception('Could not find customer module.');
        }

        return $window;
    }
}
