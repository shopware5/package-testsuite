<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class CustomerModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Customer';

    /**
     * Helper function that skips the intro wizard of the customer module
     */
    public function skipIntroWizard()
    {
        $skipButton = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Überspringen'));
        $skipButton->click();
    }

    /**
     * Fill the new customer form of the backend module with
     * the supplied data.
     *
     * @param array $data
     * @throws \Exception
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
     * @throws \Exception
     */
    public function fillEditCustomerFormWith(array $data)
    {
        $window = $this->getEditCustomerWindow();

        $this->fillCustomerForm($window, $data);
    }

    /**
     * Fill a form within the supplied window with the supplied form data
     *
     * @param NodeElement $window
     * @param array $data
     * @throws \Exception
     */
    private function fillCustomerForm(NodeElement $window, array $data)
    {
        // Fill most form elements
        $this->fillExtJsForm($window, $data);

        // Fill custom payment combobox element
        foreach ($data as $entry) {
            if ($entry['type'] !== 'paymentbox') {
                continue;
            }

            $combobox = $window->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel($entry['label']));
            $this->fillPaymentCombobox($combobox, $entry['value']);
        }
    }

    /**
     * Special helper method that fills an extJS payment info combobox
     *
     * @param NodeElement $combobox
     * @param string $value
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
        foreach ($options as $option) {
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

        if (!$window) {
            throw new \RuntimeException('Could not find customer module.');
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

        if (!$window) {
            throw new \RuntimeException('Could not find customer module.');
        }

        return $window;
    }
}
