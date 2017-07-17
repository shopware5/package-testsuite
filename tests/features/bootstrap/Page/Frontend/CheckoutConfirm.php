<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Helper\ContextAwarePage;
use Shopware\Tests\Mink\Element\AddressManagementAddressBox;
use Shopware\Tests\Mink\Element\CheckoutPayment;
use Shopware\Tests\Mink\Element\CheckoutShipping;
use Shopware\Tests\Mink\Helper;
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
     * Returns the order number from finish page
     * @return int
     */
    public function getOrderNumber()
    {
        $elements = Helper::findElements($this, ['orderNumber']);

        $orderDetails = $elements['orderNumber']->getText();

        preg_match("/\d+/", $orderDetails, $orderNumber);
        $orderNumber = intval($orderNumber[0]);

        return $orderNumber;
    }

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout()
    {
        $this->checkField('sAGB');
        $this->findButton('Zahlungspflichtig bestellen')->click();
    }

    /**
     * Opens the address change form
     */
    public function openAddressChangeForm()
    {
        /** @var CheckoutShipping $element */
        $element = $this->getElement('CheckoutShipping');

        $xPath = $element->getXPathSelectors();

        $combinedChangeButton = $xPath['combinedChangeButton'];

        $changeButton = $this->find('xpath', $combinedChangeButton);
        $changeButton->click();
    }

    /**
     * Changes the shipping or payment method
     * @param string $subject shipping or payment
     * @param int|string $method
     * @param TableNode $table
     */
    public function changeShippingOrPaymentMethod($subject, $method, TableNode $table = null)
    {
        $changeButtonXpath = $this->getXPathSelectors()['changePaymentButton'];

        $this->waitForSelectorPresent('xpath', $changeButtonXpath);

        $this->clickLink('Ändern');

        $this->waitForSelectorNotPresent('xpath', $changeButtonXpath);

        if (!is_numeric($method)) {
            $this->waitForText($method);
            $method = $this->getMethodId($subject, $method);
        }

        $data = [
            [
                'field' => $subject == 'payment' ? 'payment' : 'sDispatch',
                'value' => $method
            ]
        ];

        if ($table) {
            $data = array_merge($data, $table->getHash());
        }

        Helper::fillForm($this, 'shippingPaymentForm', $data);

        $this->spin(function (ContextAwarePage $context) {
            try {
                $this->findButton('Weiter')->click();
            } catch (\Exception $e) {
                return false;
            }
            return true;
        }, 10);
    }

    /**
     * Creates a new address and saves it
     * @param $values
     */
    public function createArbitraryAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('css', '.address--form-actions > button');
        $button->press();
    }

    /**
     * Changes the values in a modal address form and saves the form
     * @param $values
     */
    public function changeModalAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('named', ['button', 'Adresse speichern']);
        $button->press();
    }

    private function getMethodId($subject, $methodName)
    {
        if ($subject == 'shipping') {
            $classPrefix = 'dispatch';
            $inputName = 'sDispatch';
        } elseif ($subject == 'payment') {
            $classPrefix = 'payment';
            $inputName = $classPrefix;
        } else {
            throw new \Exception(sprintf('Unknown subject: %s', $subject));
        }

        $inputXpath = (new FrontendXpathBuilder())
            ->reset()
            ->child('label', ['@text' => $methodName, 'and', '~class' => 'method--name'])
            ->ancestor('div', ['~class' => $classPrefix . '--method'], 1)
            ->descendant('input', ['@name' => $inputName])
            ->getXpath();

        $input = $this->find('xpath', $inputXpath);
        if (null === $input) {
            throw new \Exception(sprintf("Could not find ID for %s '%s' on current page", $subject, $methodName));
        }

        return $input->getAttribute('value');
    }
}
