<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class CheckoutShippingPayment extends ContextAwarePage
{
    protected $path = '/checkout/shippingPayment/';

    /**
     * Change the currently selected shipping method
     *
     * @param string $shippingMethod
     */
    public function changeShippingMethodTo($shippingMethod)
    {
        if(!$this->verifyUrl()) {
            $this->open();
        }

        $element = $this->getMethodElement($shippingMethod);
        $element->click();

        $this->getPage('CheckoutConfirm')->open();
    }

    /**
     * Change the currently selected payment method
     *
     * @param string $paymentMethod
     */
    public function changePaymentMethodTo($paymentMethod)
    {
        if(!$this->verifyUrl()) {
            $this->open();
        }

        $element = $this->getMethodElement($paymentMethod);
        $element->click();

        // Fill out SEPA information if necessary
        if($paymentMethod === 'SEPA') {
            $ibanInputXpath = FrontendXpathBuilder::getInputById('iban');
            $this->waitForSelectorPresent('xpath', $ibanInputXpath);
            $this->find('xpath', $ibanInputXpath)->setValue('DE27100777770209299700');

            $bicInputXpath = FrontendXpathBuilder::getInputById('bic');
            $this->find('xpath', $bicInputXpath)->setValue('DEMOBIC');

            $bankInputXpath = FrontendXpathBuilder::getInputById('bank');
            $this->find('xpath', $bankInputXpath)->setValue('Bank');
        }

        $this->findButton('Weiter')->click();
    }

    /**
     * Return the shipping or payment method with the given name
     *
     * @param string $methodName
     * @return NodeElement|null
     */
    public function getMethodElement($methodName)
    {
        $elementXpath = FrontendXpathBuilder::create()
            ->child('label', ['@text' => $methodName])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $elementXpath);

        return $this->find('xpath', $elementXpath);
    }
}
