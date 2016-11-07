<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: YouTube
 * Location: Emotion element for Youtube videos
 *
 * Available retrievable properties:
 * - code (string, e.g. "RVz71XsJIEA")
 */
class YouTube extends MultipleElement implements HelperSelectorInterface
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
    protected $selector = ['css' => 'div.emotion--element.youtube-element'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'code' => 'iframe'
        ];
    }

    /**
     * Returns the video code
     * @return array
     */
    public function getCodeProperty()
    {
        $elements = Helper::findElements($this, ['code']);
        return $elements['code']->getAttribute('src');
    }
}
