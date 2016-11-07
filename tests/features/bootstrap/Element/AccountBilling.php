<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: AccountBilling
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountBilling extends Element implements HelperSelectorInterface
{

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
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--billing.account--box'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressData' => 'p'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'otherButton' => ['de' => 'Andere wählen', 'en' => 'Select other'],
            'changeButton' => ['de' => 'Rechnungsadresse ändern', 'en' => 'Change billing address']
        ];
    }

    /**
     * Returns the address elements
     * @return Element[]
     */
    public function getAddressProperty()
    {
        $elements = Helper::findAllOfElements($this, ['addressData']);

        return $elements['addressData'];
    }
}
