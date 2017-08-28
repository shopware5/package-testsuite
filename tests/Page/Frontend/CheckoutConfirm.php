<?php

namespace Shopware\Page\Frontend;

use Shopware\Component\Form\FormFillerTrait;
use Shopware\Page\ContextAwarePage;

class CheckoutConfirm extends ContextAwarePage
{
    use FormFillerTrait;

    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout()
    {
        $this->open();
        $this->checkField('sAGB');
        $button = $this->findButton('Zahlungspflichtig bestellen');
        $button->focus();
        $button->click();
    }

    /**
     * Fill out the registration form during checkout
     *
     * @param array $formData
     */
    public function fillOutRegistrationForm($formData)
    {
        $this->fillForm($this, $formData);
    }
}
