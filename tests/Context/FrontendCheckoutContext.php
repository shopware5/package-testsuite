<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Frontend\Account;
use Shopware\Page\Frontend\CheckoutCart;
use Shopware\Page\Frontend\CheckoutConfirm;
use Shopware\Page\Frontend\CheckoutShippingPayment;

class FrontendCheckoutContext extends SubContext
{
    /**
     * @Given the aggregations should look like this:
     * @param TableNode $aggregations
     */
    public function theCartAggregationsShouldLookLikeThis(TableNode $aggregations)
    {
        $aggregations = $aggregations->getHash();
        /** @var CheckoutCart $checkoutCart */
        $checkoutCart = $this->getPage('CheckoutCart');
        $checkoutCart->checkAggregation($aggregations);
    }

    /**
     * @When I fill in the registration form:
     * @param TableNode $customerData
     */
    public function iFillInTheRegistrationForm(TableNode $customerData)
    {
        /** @var CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');
        $page->fillOutRegistrationForm($customerData->getHash());
    }

    /**
     * @When I add the article :number to my basket
     * @param string $number
     */
    public function iAddTheArticleToMyBasket($number)
    {
        /** @var CheckoutCart $checkoutCart */
        $checkoutCart = $this->getPage('CheckoutCart');
        $checkoutCart->addArticle($number);
        $this->waitForText($number);
    }

    /**
     * @When I remove the article on position :position
     * @param string $position
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $page->removeCartPositionAtIndex($position);
    }

    /**
     * @When I proceed to order confirmation
     */
    public function iProceedToOrderConfirmation()
    {
        /** @var CheckoutCart $frontendCheckoutCart */
        $frontendCheckoutCart = $this->getPage('CheckoutCart');
        $frontendCheckoutCart->open();
        $frontendCheckoutCart->proceedToOrderConfirmation();
    }

    /**
     * @When I proceed to checkout
     */
    public function iProceedToCheckout()
    {
        /** @var CheckoutConfirm $frontendCheckoutConfirm */
        $frontendCheckoutConfirm = $this->getPage('CheckoutConfirm');
        $frontendCheckoutConfirm->proceedToCheckout();
    }

    /**
     * @When I proceed to checkout cart
     */
    public function iProceedToCheckoutCart()
    {
        /** @var CheckoutCart $frontendCheckoutCart */
        $frontendCheckoutCart = $this->getPage('CheckoutCart');
        $frontendCheckoutCart->open();
        $frontendCheckoutCart->proceedToOrderConfirmation();
    }

    /**
     * @When I proceed to checkout Confirmation
     */
    public function iProceedToCheckoutConfirmation()
    {
        /** @var CheckoutConfirm $frontendCheckoutConfirmation */
        $frontendCheckoutConfirmation = $this->getPage('CheckoutConfirm');
        $frontendCheckoutConfirmation->open();
    }

    /**
     * @Given the cart contains the following products:
     * @param TableNode $items
     * @throws \Exception
     */
    public function theCartContainsTheFollowingProducts(TableNode $items)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart')->open();
        $page->emptyCart();
        $page->fillCartWithProducts($items->getHash());
        $page->open();
        $page->validateCart($items->getHash());
    }

    /**
     * @Given I change my payment method to :paymentMethod
     * @param string $paymentMethod
     */
    public function changePaymentMethodTo($paymentMethod)
    {
        /** @var CheckoutShippingPayment $page */
        $page = $this->getPage('CheckoutShippingPayment');
        $page->open();
        $page->changePaymentMethodTo($paymentMethod);
    }

    /**
     * @Given /^I change my shipping method to "([^"]*)"(?::)?$/
     * @param string $shippingMethod
     */
    public function changeShippingMethodTo($shippingMethod)
    {
        /** @var CheckoutShippingPayment $page */
        $page = $this->getPage('CheckoutShippingPayment');
        $page->open();
        $page->changeShippingMethodTo($shippingMethod);
    }

    /**
     * @Given I am not logged in
     * @And I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        /** @var Account $page */
        $page = $this->getPage('Account');
        $page->open();

        // See if we already are logged out
        if ($this->waitIfThereIsText('Einloggen', 3)) {
            return;
        }

        $page->logout();
    }
}
