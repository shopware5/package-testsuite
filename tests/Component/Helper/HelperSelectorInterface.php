<?php

namespace Shopware\Component\Helper;

interface HelperSelectorInterface
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
    public function getXPathSelectors();

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
    public function getCssSelectors();
}
