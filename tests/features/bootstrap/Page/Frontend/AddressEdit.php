<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Shopware\Tests\Mink\Helper;
use Shopware\Component\Helper\HelperSelectorInterface;

class AddressEdit extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address/edit';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressForm' => 'div.address-form--panel',
        ];
    }

    public function getNamedSelectors()
    {
        return [
            'saveAddressButton' => ['de' => 'Adresse speichern', 'en' => 'Save address']
        ];
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
