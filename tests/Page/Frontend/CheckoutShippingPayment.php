<?php

declare(strict_types=1);

namespace Shopware\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class CheckoutShippingPayment extends ContextAwarePage
{
    protected $path = '/checkout/shippingPayment/';

    /**
     * Change the currently selected shipping method
     */
    public function changeShippingMethodTo(string $shippingMethod): void
    {
        if (!$this->verifyUrl()) {
            $this->open();
        }

        $this->selectShippingMethod($shippingMethod);

        $this->waitForJsOverlayToClose();

        $this->pressButton('Weiter');
    }

    /**
     * Change the currently selected payment method
     */
    public function changePaymentMethodTo(string $paymentMethod): void
    {
        if (!$this->verifyUrl()) {
            $this->open();
        }

        $this->selectPaymentMethod($paymentMethod);

        $this->waitForJsOverlayToClose();

        $this->pressButton('Weiter');
    }

    /**
     * Return the shipping or payment method with the given name
     */
    public function getMethodElement(string $methodName): NodeElement
    {
        $elementXpath = FrontendXpathBuilder::create()
            ->child('label', ['@text' => $methodName])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $elementXpath);

        return $this->find('xpath', $elementXpath);
    }

    /**
     * Verifies that we currently are on the shipping/payment page
     */
    protected function verifyUrl(array $urlParameters = []): bool
    {
        return $this->getDriver()->getCurrentUrl() === $this->getUrl($urlParameters);
    }

    /**
     * Select a shipping method from the list in the frontend
     */
    private function selectShippingMethod(string $shippingMethod): void
    {
        $element = $this->getMethodElement($shippingMethod);
        $element->click();
    }

    /**
     * Select a payment method from the list in the frontend and
     * fill in some demo SEPA data if necessary
     */
    private function selectPaymentMethod(string $paymentMethod): void
    {
        $element = $this->getMethodElement($paymentMethod);
        $element->click();

        // Fill out SEPA information if necessary
        if ($paymentMethod === 'SEPA') {
            $ibanInputXpath = FrontendXpathBuilder::getInputById('iban');
            $this->waitForSelectorPresent('xpath', $ibanInputXpath);

            $this->fillField('iban', 'DE27100777770209299700');
            $this->fillField('bic', 'DEMOBIC');
            $this->fillField('bank', 'Demobank');
        }
    }

    /**
     * Wait for the Javascript Overlay to close, indicating the new shipping/payment
     * method was selected successfully.
     */
    private function waitForJsOverlayToClose(): void
    {
        $this->waitForSelectorInvisible(
            'xpath',
            FrontendXpathBuilder::create()
                ->child('div', ['~class' => 'js--overlay'])
                ->getXpath()
        );
    }
}
