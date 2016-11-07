<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: Paging
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class Paging extends Element implements HelperSelectorInterface
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
    protected $selector = ['css' => 'div.listing--paging'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'previous' => 'a.pagination--link.paging--prev',
            'next' => 'a.pagination--link.paging--next'
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
     * @param $direction
     * @param int $steps
     */
    public function moveDirection($direction, $steps = 1)
    {
        $locator = array(strtolower($direction));
        $elements = Helper::findElements($this, $locator);

        for ($i = 0; $i < $steps; $i++) {
            $result = Helper::countElements($this, $direction, 1);

            if ($result !== true) {
                $result = Helper::countElements($this, $direction, 2);
            }

            if ($result !== true) {
                Helper::throwException(
                    array(sprintf('There is no more "%s" button! (after %d steps)', $direction, $i))
                );
            }

            $elements[$direction]->click();
        }
    }

    /**
     * @param integer $page
     */
    public function moveToPage($page)
    {
        while (!$this->hasLink($page)) {
            if ($this->noElement('next', false)) {
                $message = sprintf('Page %d was not found!', $page);
                Helper::throwException($message);
                return;
            }
            $this->moveDirection('next');
        }

        $this->clickLink($page);
    }

    /**
     * @param string $locator
     * @param  bool $throwException
     * @return bool
     */
    public function noElement($locator, $throwException = true)
    {
        if (Helper::getRequiredSelector($this, $locator)) { //previous or next
            $result = Helper::countElements($this, $locator);
        } else { //page number (1, 2, 3, 4, ...)
            $result = !$this->hasLink($locator);
        }

        if ($result === true) {
            return true;
        }

        if ($throwException) {
            $message = sprintf('The Paging Link "%s" exists, but should not!', $locator);
            Helper::throwException($message);
        }

        return false;
    }
}
