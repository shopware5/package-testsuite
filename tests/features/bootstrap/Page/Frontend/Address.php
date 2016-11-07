<?php
namespace Shopware\Tests\Mink\Page\Frontend;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Address extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressForm' => 'div.account--address-form form'
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

    /**
     * Returns an array of all named selectors of the element/page
     *
     * Example:
     * return [
     *  'submit' = ['de' = 'Absenden',     'en' = 'Submit'],
     *  'reset'  = ['de' = 'Zurücksetzen', 'en' = 'Reset']
     * ]
     *
     * @return array[]
     */
    public function getNamedSelectors()
    {
        return [];
    }
}
