<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class PaymentModule extends BackendModule
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Payment';

    /** @var string */
    protected $moduleWindowTitle = 'Zahlungsarten';

    /**
     * Activate a given payment method
     *
     * @param string $name
     */
    public function activatePaymentMethod($name)
    {
        $this->open();
        $window = $this->getModuleWindow();

        $paymentMethodXpath = $this->getPaymentMethodXpath($name);

        $paymentMethod = $this->waitForSelectorPresent('xpath', $paymentMethodXpath);
        $paymentMethod->click();

        $generalTab = $window->find('xpath', BackendXpathBuilder::getTabXpathByLabel('Generell'));
        $generalTab->click();

        $checkbox = $window->getCheckbox('Aktiv:');
        if ($checkbox->isChecked()) {
            return;
        }

        $checkbox->toggle();

        $window->findButton('Speichern')->click();
        $this->waitForText('Zahlungsart gespeichert', 1);
    }

    /**
     * @param $name
     * @return string
     */
    private function getPaymentMethodXpath($name)
    {
        $paymentMethodXpath = BackendXpathBuilder::create()
            ->child('div', ['starts-with' => ['@id', 'payment-main-tree']])
            ->descendant('div', ['~class' => 'x-grid-cell-inner', 'and', '~text' => $name])
            ->getXpath();

        return $paymentMethodXpath;
    }
}

//div[starts-with(@id, 'payment-main-tree')]/descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' x-grid-cell-inner ') and ./descendant-or-self::*[text()[contains(.,'SEPA (')]]]