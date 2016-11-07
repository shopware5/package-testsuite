<?php
namespace  Shopware\Tests\Mink\Page\Frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\Element\AddressManagementAddressBox;
use Shopware\Tests\Mink\Element\CheckoutPayment;
use Shopware\Tests\Mink\Element\CheckoutShipping;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class CheckoutConfirm extends ContextAwarePage implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

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
            'confirmButton' => ['de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order'],
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
        $language = Helper::getCurrentLanguage();

        try {
            $assert = new WebAssert($this->getSession());
            $assert->pageTextContains($namedSelectors['gtc'][$language]);
        } catch (ResponseTextException $e) {
            $message = ['You are not on the checkout confirmation page!', 'Current URL: ' . $this->getDriver()->getCurrentUrl()];
            Helper::throwException($message);
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
        Helper::pressNamedButton($this, 'confirmButton');
    }

    /**
     * Changes the billing address
     * @param array $data
     */
    public function changeBillingAddress(array $data = [])
    {
        $element = $this->getElement('CheckoutBilling');
        Helper::clickNamedLink($element, 'changeButton');

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'billingForm', $data);
        Helper::pressNamedButton($account, 'changeBillingButton');
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
     * Fills the shipping address in an already open form
     * @param array $data
     */
    public function changeAddress(array $data = [])
    {
        /** @var AddressManagementAddressBox $element */
        $element = $this->getElement('AddressManagementAddressBox');

        Helper::fillForm($element, 'form', $data);
        Helper::pressNamedButton($element, 'saveAddressButton');
    }

    /**
     * Fills the shipping address in an already open form
     * @param array $data
     */
    public function changeShippingAddress(array $data = [])
    {
        $account = $this->getPage('Account');

        Helper::fillForm($account, 'shippingForm', $data);
        Helper::pressNamedButton($account, 'changeShippingButton');
    }

    /**
     * Changes the shipping or payment method
     * @param string $subject shipping or payment
     * @param int|string $method
     * @param TableNode $table
     */
    public function changeShippingOrPaymentMethod($subject, $method, TableNode $table = null)
    {
        $element = $this->getElement('CheckoutPayment');

        $this->spin(function (ContextAwarePage $context) use ($element) {
            try{
                Helper::clickNamedLink($element, 'changeButton');
            } catch (\Exception $e){
                return false;
            }
            return true;
        }, 10);

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
            try{
                Helper::pressNamedButton($this, 'changePaymentButton');
            } catch (\Exception $e){
                return false;
            }
            return true;
        }, 10);
    }

    /**
     * Checks the name of the current payment method
     * @param string $paymentMethod
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var CheckoutPayment $element */
        $element = $this->getElement('CheckoutPayment');

        $properties = [
            'paymentMethod' => $paymentMethod
        ];

        $result = Helper::assertElementProperties($element, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The current payment method is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
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

    /**
     * Returns an array of all xpath selectors of the element/page
     *
     * Example:
     * return [
     *  'loginform' = "//input[@id='email']/ancestor::form[1]",
     *  'loginemail' = "//input[@name='email']",
     *  'password' = "//input[@name='password']",
     * ]
     *
     * @return string[]
     */
    public function getXPathSelectors()
    {
        return [];
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

        $xp = new XpathBuilder();
        $inputXpath = $xp
            ->label(['@text' => $methodName, 'and', '~class' => 'method--name'])
            ->div('asc', ['~class' => $classPrefix.'--method'], 1)
            ->input('desc', ['@name' => $inputName])
            ->get();
        $input = $this->find('xpath', $inputXpath);
        if (null === $input) {
            throw new \Exception(sprintf("Could not find ID for %s '%s' on current page", $subject, $methodName));
        }
        return $input->getAttribute('value');
    }
}
