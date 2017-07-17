<?php

namespace Shopware\Element\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Component\Helper\HelperSelectorInterface;

class CheckoutShipping extends Element implements HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.shipping--panel'];

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
            'changeButton'  => ['de' => 'Adresse ändern', 'en' => 'Change address'],
            'otherButton'  => ['de' => 'Andere', 'en' => 'Others']
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
        return [
            'combinedChangeButton' => '//div[contains(text(), "Rechnungs- und Lieferadresse")]//ancestor::form[1]//a[@data-title="Adresse ändern"]',
        ];
    }
}
