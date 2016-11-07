<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: CheckoutPayment
 * Location: Payment box on checkout confirm page
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CheckoutPayment extends Element implements HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.payment--panel'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'span.payment--description'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton' => ['de' => 'Ändern', 'en' => 'Change']
        ];
    }

    /**
     * Returns the current payment method
     * @return string
     */
    public function getPaymentMethodProperty()
    {
        $element = Helper::findElements($this, ['currentMethod']);

        return $element['currentMethod']->getText();
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
