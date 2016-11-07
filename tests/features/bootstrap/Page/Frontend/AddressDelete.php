<?php
namespace Shopware\Tests\Mink\Page\Frontend;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class AddressDelete extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address/delete';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'panelTitle' => 'h1.panel--title'
        ];
    }

    public function getNamedSelectors()
    {
        return [
            'confirmDeleteButton' => ['de' => 'Bestätigen', 'en' => 'Confirm']
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
