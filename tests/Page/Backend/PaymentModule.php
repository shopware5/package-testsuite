<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use RuntimeException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class PaymentModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Payment';

    protected string $moduleWindowTitle = 'Zahlungsarten';

    /**
     * Activate a given payment method
     */
    public function activatePaymentMethod(string $name): void
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

        $saveButton = $window->findButton('Speichern');
        if (!$saveButton instanceof NodeElement) {
            throw new RuntimeException('Could not find save button');
        }
        $saveButton->click();
        $this->waitForText('Zahlungsart gespeichert', 1);
    }

    private function getPaymentMethodXpath(string $name): string
    {
        return BackendXpathBuilder::create()
            ->child('div', ['starts-with' => ['@id', 'payment-main-tree']])
            ->descendant('div', ['~class' => 'x-grid-cell-inner', 'and', '~text' => $name])
            ->getXpath();
    }
}
