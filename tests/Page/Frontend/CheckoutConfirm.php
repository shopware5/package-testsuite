<?php

namespace Shopware\Page\Frontend;

use Shopware\Component\Form\FormFillerTrait;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;
use Shopware\Component\Helper\HelperSelectorInterface;

class CheckoutConfirm extends ContextAwarePage implements HelperSelectorInterface
{
    use FormFillerTrait;

    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [
            'changePaymentButton' => (new FrontendXpathBuilder())
                ->reset()
                ->child('form', ['@id' => 'confirm--form'])
                ->descendant('a', ['~class' => 'btn--change-payment'])
                ->getXpath(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'shippingPaymentForm' => 'form.payment',
            'proceedCheckoutForm' => 'form#confirm--form',
            'orderNumber' => 'div.finish--details > div.panel--body',
            'addressForm' => 'form[name="frmAddresses"]',
            'company' => '.address--company',
            'address' => '.address--address',
            'salutation' => '.address--salutation',
            'customerTitle' => '.address--title',
            'firstname' => '.address--firstname',
            'lastname' => '.address--lastname',
            'street' => '.address--street',
            'addLineOne' => '.address--additional-one',
            'addLineTwo' => '.address--additional-two',
            'zipcode' => '.address--zipcode',
            'city' => '.address--city',
            'stateName' => '.address--statename',
            'countryName' => '.address--countryname',
        ];
    }

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout()
    {
        $this->open();
        $this->checkField('sAGB');
        $this->findButton('Zahlungspflichtig bestellen')->click();
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
