<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Helper\ContextAwarePage;

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
        $this->open();

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
        $this->open();

        $element = $this->getMethodElement($paymentMethod);
        $element->click();

        $this->getPage('CheckoutConfirm')->open();
    }

    /**
     * Return the shipping or payment method with the given name
     *
     * @param string $methodName
     * @return NodeElement|null
     */
    private function getMethodElement($methodName)
    {
        $elementXpath = FrontendXpathBuilder::create()
            ->child('label', ['@text' => $methodName])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $elementXpath);

        return $this->find('xpath', $elementXpath);
    }
}
