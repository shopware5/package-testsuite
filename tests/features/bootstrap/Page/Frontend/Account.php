<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\WebAssert;
use Shopware\Component\XpathBuilder\BaseXpathBuilder;
use Shopware\Tests\Mink\Element\AccountPayment;
use Shopware\Tests\Mink\Element\AddressBox;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Component\Helper\HelperSelectorInterface;

class Account extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'payment' => 'div.account--payment.account--box strong',
            'logout' => 'div.account--menu-container a.link--logout',
            'registrationForm' => 'form.register--form',
            'billingForm' => 'div.account--address-form form',
            'shippingForm' => 'div.account--address-form form',
            'paymentForm' => 'div.account--payment-form > form',
            'passwordForm' => 'div.profile-password--container > form',
            'emailForm' => 'div.profile-email--container > form',
            'profileForm' => 'div.account--profile > form',
            'changePasswordButton' => 'div.profile-password--container button',
            'changeEmailButton' => 'div.profile-email--container button',
            'changeProfileButton' => 'div.account--profile > form button',
            'esdDownloads' => '.downloads--table-header ~ .panel--tr',
            'esdDownloadName' => '.download--name'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'loginButton'           => ['de' => 'Anmelden',                 'en' => 'Login'],
            'forgotPasswordLink'    => ['de' => 'Passwort vergessen?',      'en' => 'Forgot your password?'],
            'sendButton'            => ['de' => 'Weiter',                   'en' => 'Continue'],

            'myAccountLink'         => ['de' => 'Übersicht',                'en' => 'Overview'],
            'profileLink'           => ['de' => 'Persönliche Daten',        'en' => 'Profile'],
            'addressesLink'         => ['de' => 'Adressen',                 'en' => 'Addresses'],
            'myOrdersLink'          => ['de' => 'Bestellungen',             'en' => 'orders'],
            'myEsdDownloadsLink'    => ['de' => 'Sofortdownloads',          'en' => 'Instant downloads'],
            'changePaymentLink'     => ['de' => 'Zahlungsarten',            'en' => 'Payment methods'],
            'noteLink'              => ['de' => 'Merkzettel',               'en' => 'Wish list'],
            'logoutLink'            => ['de' => 'Abmelden',                 'en' => 'Logout'],

            'changePaymentButton'   => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeBillingButton'   => ['de' => 'Adresse speichern',        'en' => 'Change address'],
            'changeShippingButton'  => ['de' => 'Adresse speichern',        'en' => 'Change address'],
            'saveAddressButton'     => ['de' => 'Adresse speichern',        'en' => 'Save address']
        ];
    }

    /**
     * Logins a user
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        Helper::pressNamedButton($this, 'loginButton');
    }


    /**
     * Logout a customer (important when using the Selenium driver)
     */
    public function logout()
    {
        $this->getDriver()->visit('/account/logout');
    }

    /**
     * Changes the password of the user
     * @param string $currentPassword
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function changePassword($currentPassword, $password, $passwordConfirmation = null)
    {
        $data = [
            [
                'field' => 'password[currentPassword]',
                'value' => $currentPassword
            ],
            [
                'field' => 'password[password]',
                'value' => $password
            ],
            [
                'field' => 'password[passwordConfirmation]',
                'value' => ($passwordConfirmation !== null) ? $passwordConfirmation : $password
            ]
        ];

        Helper::fillForm($this, 'passwordForm', $data);
        $this->find('css', $this->getCssSelectors()['changePasswordButton'])->press();
    }

    /**
     * Changes the email address of the user
     * @param string $password
     * @param string $email
     * @param string $emailConfirmation
     */
    public function changeEmail($password, $email, $emailConfirmation = null)
    {
        $data = [
            [
                'field' => 'email[currentPassword]',
                'value' => $password
            ],
            [
                'field' => 'email[email]',
                'value' => $email
            ],
            [
                'field' => 'email[emailConfirmation]',
                'value' => ($emailConfirmation !== null) ? $emailConfirmation : $email
            ]
        ];

        Helper::fillForm($this, 'emailForm', $data);
        $this->find('css', $this->getCssSelectors()['changeEmailButton'])->press();
    }

    /**
     * Changes the billing address of the user
     * @param array $values
     */
    public function changeBillingAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Changes the shipping address of the user
     * @param array $values
     */
    public function changeShippingAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Creates a new address used neither as billing nor as shipping address
     * @param array $values
     */
    public function createArbitraryAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Changes the payment method
     * @param array $data
     * @param TableNode $table
     */
    public function changePaymentMethod($data, TableNode $table = null)
    {
        if (!is_numeric($data[0]['value'])) {
            $data[0]['value'] = $this->getMethodId('payment', $data[0]['value']);
        }
        $element = $this->getElement('AccountPayment');
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'paymentForm', $data);
        if ($table) {
            
//            throw new \Exception(print_r($table,true));
        }
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     *
     *
     * @param string $subject
     * @param string $methodName
     * @return string
     * @throws \Exception
     */
    private function getMethodId($subject, $methodName)
    {
        // Set prefixes
        switch ($subject) {
            case 'shipping':
                $classPrefix = 'dispatch';
                $inputName = 'sDispatch';
                break;
            case 'payment':
                $classPrefix = 'payment';
                $inputName = $classPrefix;
                break;
            default:
                throw new \Exception(sprintf('Unknown subject: %s', $subject));
        }

        // Find element on page
        $builder = new BaseXpathBuilder();
        $inputXpath = $builder
            ->child('label', ['@text' => $methodName, 'and', '~class' => 'method--name'])
            ->ancestor('div', ['~class' => $classPrefix.'--method'], 1)
            ->descendant('input', ['@name' => $inputName])
            ->getXpath();
        $input = $this->find('xpath', $inputXpath);

        if (null === $input) {
            throw new \Exception(sprintf("Could not find ID for %s '%s' on current page", $subject, $methodName));
        }

        // Get ID attribute
        return $input->getAttribute('value');
    }

    /**
     * Fills the fields of the registration form and submits it
     * @param array $data
     */
    public function register(array $data)
    {
        Helper::fillForm($this, 'registrationForm', $data);
        Helper::pressNamedButton($this, 'sendButton');
    }

    /**
     * @param AddressBox $addresses
     * @param string $name
     */
    public function chooseAddress(AddressBox $addresses, $name)
    {
        $this->searchAddress($addresses, $name);
    }

    /**
     * @param AddressBox $addresses
     * @param string $name
     * @throws \Exception
     */
    protected function searchAddress(AddressBox $addresses, $name)
    {
        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            if (strpos($address->getProperty('title'), $name) === false) {
                continue;
            }

            Helper::pressNamedButton($address, 'chooseButton');

            return;
        }

        $messages = ['The address "' . $name . '" is not available. Available are:'];

        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            $messages[] = $address->getProperty('title');
        }

        Helper::throwException($messages);
    }

    /**
     * @param string $salutation
     * @param string $firstname
     * @param string $lastname
     */
    public function changeProfile($salutation, $firstname, $lastname)
    {
        $data = [
            [
                'field' => 'profile[salutation]',
                'value' => $salutation
            ],
            [
                'field' => 'profile[firstname]',
                'value' => $firstname
            ],
            [
                'field' => 'profile[lastname]',
                'value' => $lastname
            ]
        ];

        Helper::fillForm($this, 'profileForm', $data);
        $this->find('css', $this->getCssSelectors()['changeProfileButton'])->press();
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
}
