<?php

namespace Shopware\Page\Frontend;

use Shopware\Component\Form\FormFillerTrait;
use Shopware\Component\Helper\HelperSelectorInterface;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Account extends ContextAwarePage implements HelperSelectorInterface
{
    use FormFillerTrait;

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
        $this->open();

        $this->find('xpath', FrontendXpathBuilder::getElementXpathByName('input', 'email'))->setValue($email);
        $this->find('xpath', FrontendXpathBuilder::getElementXpathByName('input', 'password'))->setValue($password);

        $submitButtonXpath = FrontendXpathBuilder::create()
            ->child('button', ['~class' => 'register--login-btn'])
            ->contains('Anmelden')
            ->getXpath();

        $button = $this->find('xpath', $submitButtonXpath);
        $button->submit();
    }

    /**
     * Log the currently authenticated user out of the frontend
     */
    public function logout()
    {
        $this->open();
        $this->getDriver()->visit($this->getDriver()->getCurrentUrl() . '/logout');
    }

    /**
     * Fills the fields of the registration form and submits it
     * @param array $data
     */
    public function register(array $data)
    {
        $this->open();
        $this->fillForm($this, $data);
        $this->findButton('Weiter')->click();
    }
}
