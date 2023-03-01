<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Backend extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $path = '/backend/';

    /**
     * Log user into the backend
     */
    public function login(string $user = 'demo', string $password = 'demo'): void
    {
        $this->open();
        $this->getSession()->setCookie('lastCheckSubscriptionDate', date('dmY'));

        // Are we already logged in?
        if ($this->waitIfThereIsText('Marketing', 3)) {
            return;
        }

        $this->waitForSelectorPresent('xpath', "//input[@name='username']");

        $userInput = $this->find('xpath', "//input[@name='username']");
        $userInput->setValue($user);

        $passwordInput = $this->find('xpath', "//input[@name='password']");
        $passwordInput->setValue($password);

        $button = $this->find('xpath', "//button[@data-action='login']");
        $button->click();

        $this->waitForText('Marketing', 5);
    }

    /**
     * Click on a tab identified by its label
     */
    public function clickOnTabWithName(string $label): void
    {
        $tab = $this->find('xpath', BackendXpathBuilder::getTabXpathByLabel($label));
        $tab->click();
    }
}
