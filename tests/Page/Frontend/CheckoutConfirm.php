<?php

declare(strict_types=1);

namespace Shopware\Page\Frontend;

use RuntimeException;
use Shopware\Component\Form\FormFillerTrait;
use Shopware\Page\ContextAwarePage;

class CheckoutConfirm extends ContextAwarePage
{
    use FormFillerTrait;

    /**
     * @var string
     */
    protected $path = '/checkout/confirm';

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout(): void
    {
        $this->open();
        $this->checkField('sAGB');
        $button = $this->findButton('Zahlungspflichtig bestellen');
        if ($button === null) {
            throw new RuntimeException('Could not find the submit button');
        }
        $button->focus();
        $button->click();
    }

    /**
     * Fill out the registration form during checkout
     */
    public function fillOutRegistrationForm(array $formData): void
    {
        $this->fillForm($this, $formData);
    }
}
