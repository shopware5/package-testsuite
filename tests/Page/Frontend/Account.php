<?php

namespace Shopware\Page\Frontend;

use Shopware\Component\Helper\HelperSelectorInterface;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Account extends ContextAwarePage implements HelperSelectorInterface
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
            'esdDownloadName' => '.download--name',
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
        $this->fillRegistrationForm($data);
        $this->findButton('Weiter')->click();
    }

    /**
     * Fill in the customer registration form
     *
     * @param array $data
     */
    private function fillRegistrationForm(array $data)
    {
        $this->open();

        foreach ($data as $formElement) {
            $elementXpath = FrontendXpathBuilder::getElementXpathByName($formElement['type'], $formElement['name']);
            $this->waitForSelectorPresent('xpath', $elementXpath);
            $element = $this->find('xpath', $elementXpath);
            if ($element->isVisible()) {
                if($formElement['type'] === 'select') {
                    $element->selectOption($formElement['value']);
                    continue;
                }

                $element->setValue($formElement['value']);
            }
        }
    }
}
