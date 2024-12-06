<?php

declare(strict_types=1);

namespace Shopware\Page\Frontend;

use Shopware\Component\Form\FormFillerTrait;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Account extends ContextAwarePage
{
    use FormFillerTrait;

    /**
     * @var string
     */
    protected $path = '/account';

    /**
     * Logs a user into the frontend
     */
    public function login(string $email, string $password): void
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
    public function logout(): void
    {
        $this->open();
        $this->getDriver()->visit($this->getDriver()->getCurrentUrl() . '/logout');
    }

    /**
     * Fills the fields of the registration form and submits it
     */
    public function register(array $data): void
    {
        $this->open();
        $this->fillForm($this, $data);
        $this->findButton('Weiter')->click();
    }
}
