<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;
use Shopware\Component\Helper\HelperSelectorInterface;

class CheckoutConfirm extends ContextAwarePage implements HelperSelectorInterface
{
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
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'gtc' => ['de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'],
            'changePaymentButton' => ['de' => 'Weiter', 'en' => 'Next'],
            'saveAsNewAddressButton' => ['de' => 'Als neue Adresse speichern'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if ($this->getDriver() instanceof Selenium2Driver) {
            $this->getDriver()->wait(5000, '$("#sAGB").length > 0');
        }

        $namedSelectors = $this->getNamedSelectors();
        $language = 'de';

        try {
            $assert = new WebAssert($this->getSession());
            $assert->pageTextContains($namedSelectors['gtc'][$language]);
        } catch (ResponseTextException $e) {
            $message = ['You are not on the checkout confirmation page!', 'Current URL: ' . $this->getDriver()->getCurrentUrl()];
            throw new \Exception($message);
        }
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
}
