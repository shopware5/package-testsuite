<?php

namespace Shopware\Page\Backend;

use Shopware\Page\ContextAwarePage;

class BackendLogin extends ContextAwarePage
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
        if ($this->waitIfThereIsText('Marketing', 5)) {
            return;
        }

        $this->waitForSelectorPresent('xpath', "//input[@name='username']");

        $userInput = $this->find('xpath', "//input[@name='username']");
        $userInput->click();
        $userInput->focus();
        $userInput->setValue($user);

        $userInput = $this->find('xpath', "//input[@name='password']");
        $userInput->click();
        $userInput->focus();
        $userInput->setValue($password);


        $button = $this->find('xpath', "//button[@data-action='login']");
        $button->focus();
        $button->click();

        //$this->getDriver()->executeScript("document.getElementsByName('username')[0].value = '" . $user . "'; document.getElementsByName('password')[0].value = '" . $password . "';document.querySelectorAll(\"[data-action='login']\")[0].click();");

        $this->waitForText('Marketing', 5);
    }
}
