<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Element\CartPosition;
use Shopware\Tests\Mink\Page\Backend\ShippingModule;
use Shopware\Tests\Mink\Page\Frontend\Account;
use Shopware\Tests\Mink\Page\Frontend\CheckoutCart;
use Shopware\Tests\Mink\Page\Frontend\CheckoutConfirm;
use Shopware\Tests\Mink\Page\Frontend\CheckoutShippingPayment;

class FrontendCheckoutContext extends SubContext
{
    /**
     * @Given the aggregations should look like this:
     * @param TableNode $aggregations
     */
    public function theAggregationsShouldLookLikeThis(TableNode $aggregations)
    {
        $aggregations = $aggregations->getHash();
        /** @var CheckoutCart $checkoutCart */
        $checkoutCart = $this->getPage('CheckoutCart');
        $checkoutCart->checkAggregation($aggregations);
    }

    /**
     * @When I add the article :article to my basket
     * @param string $article
     */
    public function iAddTheArticleToMyBasket($article)
    {
        /** @var CheckoutCart $checkoutCart */
        $checkoutCart = $this->getPage('CheckoutCart');
        $checkoutCart->addArticle($article);
        $this->waitForText($article);
    }

    /**
     * @When I remove the article on position :position
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');

        /** @var CartPosition $cartPosition */
        $cartPosition = $this->getMultipleElement($page, 'CartPosition', $position);
        $page->removeProduct($cartPosition);
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
     */
    public function theCartContainsTheFollowingProducts(TableNode $items)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart')->open();
        $page->resetCart();
        $page->fillCartWithProducts($items->getHash());
        $page->open();
        $this->theCartShouldContainTheFollowingProducts($items);
    }

    /**
     * @Then the cart should contain the following products:
     */
    public function theCartShouldContainTheFollowingProducts(TableNode $items)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');

        /** @var CartPosition $cartPositions */
        $cartPositions = $this->getMultipleElement($page, 'CartPosition');
        $page->checkCartProducts($cartPositions, $items->getHash());
    }

    /**
     * @Given /^I change the (payment|shipping) method in checkout to "([^"]*)"(?::)?$/
     * @param $subject
     * @param int|string $method
     * @param TableNode $data
     */
    public function iChangeTheShippingOrPaymentMethodTo($subject, $method, TableNode $data = null)
    {
        /** @var CheckoutConfirm $page */
        $page = $this->getPage('CheckoutConfirm');
        $page->open();
        $page->changeShippingOrPaymentMethod($subject, $method, $data);
    }

    /**
     * @Given I change my payment method to :paymentMethod
     *
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
     * @Given I change my shipping method to :shippingMethod
     *
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
     * @Given /^I change the (payment|dispatch) method in cart to "([^"]*)"(?::)?$/
     * @param $subject
     * @param int|string $method
     * @param TableNode $data
     */
    public function iChangeTheDispatchOrPaymentMethodInCartTo($subject, $method, TableNode $data = null)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $page->changeDispatchOrPaymentMethod($subject, $method, $data);
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

    /**
     * @Then /^I delete the shipping method "([^"]*)"$/
     * @param string $shippingMethod
     */
    public function iDeleteTheShippingMethod($shippingMethod)
    {
        /** @var ShippingModule $page */
        $page = $this->getPage('ShippingModule');
        $page->open();

        $page->deleteShippingMethod($shippingMethod);
    }
}
