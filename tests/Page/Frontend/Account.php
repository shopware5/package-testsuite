<?php

namespace Shopware\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\Helper\Helper;
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
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * Logs a user into the frontend
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->findButton('Anmelden')->click();
    }

    /**
     * Log the currently authenticated user out of the frontend
     */
    public function logout()
    {
        $this->getDriver()->visit('/account/logout');
    }

    /**
     * Fills the fields of the registration form and submits it
     * @param array $data
     */
    public function register(array $data)
    {
        Helper::fillForm($this, 'registrationForm', $data);
        $this->findButton('Weiter')->click();
    }
}
