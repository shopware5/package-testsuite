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

        $this->find('xpath', "//input[@name='username']")->setValue($user);
        $this->find('xpath', "//input[@name='password']")->setValue($password);
        $this->find('xpath', "//button[@data-action='login']")->click();
    }
}
