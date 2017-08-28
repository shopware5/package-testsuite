<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Backend extends ContextAwarePage
{
    /**
     * @var string $path
     */
    protected $path = '/backend/';

    /**
     * Log user into the backend
     *
     * @param string $user
     * @param string $password
     */
    public function login($user = 'demo', $password = 'demo')
    {
        $this->open();

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
     *
     * @param string $label
     */
    public function clickOnTabWithName($label)
    {
        $tab = $this->find('xpath', BackendXpathBuilder::getTabXpathByLabel($label));
        $tab->click();
    }
}
