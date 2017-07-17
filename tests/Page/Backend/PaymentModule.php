<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class PaymentModule extends BackendModule
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Payment';

    /**
     * Activate a given payment method
     *
     * @param string $name
     */
    public function activatePaymentMethod($name)
    {
        $page = $this->getPage('PaymentModule');
        $window = $this->getModuleWindow();

        $paymentMethodXpath = BackendXpathBuilder::create()
            ->child('div', ['starts-with' => ['@id', 'payment-main-tree']])
            ->descendant('div', ['~class' => 'x-grid-cell-inner', 'and', '~text' => $name . ' ('])
            ->getXpath();

        $paymentMethod = $window->find('xpath', $paymentMethodXpath);
        $paymentMethod->click();

        $generalTabXpath = BackendXpathBuilder::create()
            ->child('span', ['@class' => 'x-tab-inner', 'and', '~text' => 'Generell'])
            ->getXpath();

        $tab = $window->find('xpath', $generalTabXpath);
        $tab->click();

        $checkbox = $window->find('xpath', BackendXpathBuilder::getInputXpathByLabel('Aktiv:'));
        $checked = $checkbox->find('xpath',
            BackendXpathBuilder::create()->ancestor('table', ['~class' => 'x-form-cb-checked'], 1)->getXpath());

        if (null !== $checked) {
            return;
        }

        $checkbox->click();

        $saveButton = $page->findButton('Speichern');
        $saveButton->click();

        $this->waitForText('Zahlungsart gespeichert', 1);
    }

    /**
     * Helper method that returns the payment module window
     *
     * @return NodeElement|null
     */
    private function getModuleWindow()
    {
        /** @var PaymentModule $page */
        $page = $this->getPage('PaymentModule');
        $page->open();
        $this->waitForText('Zahlungsarten');

        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle('Zahlungsarten');
        $this->waitForSelectorPresent('xpath', $windowXpath);
        return $page->find('xpath', $windowXpath);
    }
}
