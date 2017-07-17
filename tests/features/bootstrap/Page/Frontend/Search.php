<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\Helper\HelperSelectorInterface;

class Search extends Page implements HelperSelectorInterface
{
    protected $path = 'search?sSearch={searchTerm}';

    /**
     * Deactivate verification on page open since we get redirected
     */
    protected function verify(array $urlParameters)
    {
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
        // TODO: Implement getXPathSelectors() method.
    }

    /**
     * Returns an array of all css selectors of the element/page
     *
     * Example:
     * return [
     *  'image' = 'a > img',
     *  'link' = 'a',
     *  'text' = 'p'
     * ]
     *
     * @return string[]
     */
    public function getCssSelectors()
    {
        // TODO: Implement getCssSelectors() method.
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
        // TODO: Implement getNamedSelectors() method.
    }
}
