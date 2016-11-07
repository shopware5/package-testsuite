<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: AccountPayment
 * Location: Payment box on account dashboard
 *
 * Available retrievable properties:
 * -
 */
class AccountPayment extends Element implements HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--payment.account--box'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'p'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton' => ['de' => 'Zahlungsart ändern', 'en' => 'Change payment method']
        ];
    }

    /**
     * Returns the name of the current payment method
     * @return string
     */
    public function getPaymentMethodProperty()
    {
        $element = Helper::findElements($this, ['currentMethod']);

        $currentMethod = $element['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return current($currentMethod);
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
