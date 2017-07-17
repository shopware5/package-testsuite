<?php

namespace Shopware\Element\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Component\Helper\HelperSelectorInterface;

class HeaderCart extends Element implements HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'li.navigation--entry.entry--cart'];

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
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'quantity' => 'span.cart--quantity',
            'amount' => 'span.cart--amount',
            'link' => 'a.cart--link'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     *
     * @param string $quantity
     * @param float $amount
     * @throws \Exception
     */
    public function checkCart($quantity, $amount)
    {
        $element = Helper::findElements($this, ['quantity', 'amount']);

        $check = array(
            'quantity' => array((int)$element['quantity']->getText(), $quantity),
            'amount' => Helper::floatArray(array($element['amount']->getText(), $amount))
        );

        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The %s of the header cart is wrong! (%s instead of %s)',
                $result, $check[$result][0], $check[$result][1]
            );
            throw new \Exception($message);
        }
    }

    /**
     *
     */
    public function clickCart()
    {
        $element = Helper::findElements($this, 'link');

        $element['link']->click();
    }
}
