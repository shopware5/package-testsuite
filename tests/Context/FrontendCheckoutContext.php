<?php

declare(strict_types=1);

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
     */
    public function theCartAggregationsShouldLookLikeThis(TableNode $aggregations): void
    {
        $aggregations = $aggregations->getHash();

        $checkoutCart = $this->getValidPage(CheckoutCart::class);
        $checkoutCart->checkAggregation($aggregations);
    }

    /**
     * @When I fill in the registration form:
     */
    public function iFillInTheRegistrationForm(TableNode $customerData): void
    {
        $page = $this->getValidPage(CheckoutConfirm::class);
        $page->fillOutRegistrationForm($customerData->getHash());
    }

    /**
     * @When I add the article :number to my basket
     */
    public function iAddTheArticleToMyBasket(string $number): void
    {
        $checkoutCart = $this->getValidPage(CheckoutCart::class);
        $checkoutCart->addArticle($number);
        $this->waitForText($number);
    }

    /**
     * @When I remove the article on position :position
     */
    public function iRemoveTheArticleOnPosition(string $position): void
    {
        $page = $this->getValidPage(CheckoutCart::class);
        $page->removeCartPositionAtIndex($position);
    }

    /**
     * @When I proceed to order confirmation
     */
    public function iProceedToOrderConfirmation(): void
    {
        $frontendCheckoutCart = $this->getValidPage(CheckoutCart::class);
        $frontendCheckoutCart->open();
        $frontendCheckoutCart->proceedToOrderConfirmation();
    }

    /**
     * @When I proceed to checkout
     */
    public function iProceedToCheckout(): void
    {
        $frontendCheckoutConfirm = $this->getValidPage(CheckoutConfirm::class);
        $frontendCheckoutConfirm->proceedToCheckout();
    }

    /**
     * @When I proceed to checkout cart
     */
    public function iProceedToCheckoutCart(): void
    {
        $frontendCheckoutCart = $this->getValidPage(CheckoutCart::class);
        $frontendCheckoutCart->open();
        $frontendCheckoutCart->proceedToOrderConfirmation();
    }

    /**
     * @When I proceed to checkout Confirmation
     */
    public function iProceedToCheckoutConfirmation(): void
    {
        $frontendCheckoutConfirmation = $this->getValidPage(CheckoutConfirm::class);
        $frontendCheckoutConfirmation->open();
    }

    /**
     * @Given the cart contains the following products:
     *
     * @throws \Exception
     */
    public function theCartContainsTheFollowingProducts(TableNode $items): void
    {
        $page = $this->getValidPage(CheckoutCart::class);
        $page->open();
        $page->emptyCart();
        $page->fillCartWithProducts($items->getHash());
        $page->open();
        $page->validateCart($items->getHash());
    }

    /**
     * @Given I change my payment method to :paymentMethod
     */
    public function changePaymentMethodTo(string $paymentMethod): void
    {
        $page = $this->getValidPage(CheckoutShippingPayment::class);
        $page->open();
        $page->changePaymentMethodTo($paymentMethod);
    }

    /**
     * @Given /^I change my shipping method to "([^"]*)"(?::)?$/
     */
    public function changeShippingMethodTo(string $shippingMethod): void
    {
        $page = $this->getValidPage(CheckoutShippingPayment::class);
        $page->open();
        $page->changeShippingMethodTo($shippingMethod);
    }

    /**
     * @Given I am not logged in
     *
     * @And I am not logged in
     */
    public function iAmNotLoggedIn(): void
    {
        $page = $this->getValidPage(Account::class);
        $page->open();

        // See if we already are logged out
        if ($this->waitIfThereIsText('Einloggen', 3)) {
            return;
        }

        $page->logout();
    }
}
