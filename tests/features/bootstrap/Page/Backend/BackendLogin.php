<?php

namespace Shopware\Tests\Mink\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\HelperSelectorInterface;

class BackendLogin extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/backend/';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [
            'loginUsernameInput' => "//input[@name='username']",
            'loginUsernamepassword' => "//input[@name='password']",
            'loginLoginButton' => "//button[@data-action='login']",
        ];
    }

    public function login($user, $password)
    {
        $xpath = $this->getXPathSelectors();
        $this->find('xpath', $xpath['loginUsernameInput'])->setValue($user);
        $this->find('xpath', $xpath['loginUsernamepassword'])->setValue($password);
        $this->find('xpath', $xpath['loginLoginButton'])->click();
    }
}
