<?php

declare(strict_types=1);

namespace Shopware\Page;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\SpinTrait\SpinTrait;

class ContextAwarePage extends Page
{
    use SpinTrait;

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     *
     * @throws \Exception
     */
    protected function waitForText(string $text, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(text(), '$text')]");

            return \count($result) > 0;
        });
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitIfThereIsText(string $text, int $wait = 5): bool
    {
        return $this->spinWithNoException(function (self $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, $wait);
    }

    /**
     * Checks via a string exists
     */
    protected function checkIfThereIsText(string $text, ContextAwarePage $context): bool
    {
        $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(., '$text')]");

        return !empty($result);
    }

    /**
     * Checks via spin function if a locator is present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     *
     * @throws \Exception
     */
    protected function waitForSelectorPresent(string $selector, string $locator, int $sleep = 2): NodeElement
    {
        sleep($sleep);
        $elem = null;
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator, &$elem) {
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            if ($elem === null) {
                return false;
            }

            return true;
        });

        if (!$elem instanceof NodeElement) {
            throw new ElementNotFoundException($this->getDriver(), null, $selector, $locator);
        }

        return $elem;
    }

    /**
     * Checks via spin function if a locator is not present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     *
     * @throws \Exception
     */
    protected function waitForSelectorNotPresent(string $selector, string $locator, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            if ($elem === null) {
                return true;
            }

            return false;
        });
    }

    /**
     * Checks via spin function if an element is present on page via given xpath, with sleep at the beginning (default 2)
     *
     * @throws \Exception
     */
    protected function waitForXpathElementPresent(string $xpath, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($xpath) {
            $elem = $context->getSession()->getPage()->find('xpath', $xpath);
            if ($elem === null) {
                return false;
            }

            return true;
        });
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     *
     * @throws \Exception
     */
    protected function waitForTextNotPresent(string $text, int $sleep = 2): void
    {
        $this->waitForSelectorNotPresent('xpath', "//*[contains(text(), '$text')]", $sleep);
    }

    /**
     * Checks via spin function if a locator is visible on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     *
     * @throws \Exception
     */
    protected function waitForSelectorVisible(string $selector, string $locator): void
    {
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            $context->getSession()->getPage()->find($selector, $locator);

            return true;
        }, 90);
    }

    /**
     * Checks via spin function if a locator is invisible on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     */
    protected function waitForSelectorInvisible(string $selector, string $locator, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            $elem = $context->getSession()->getPage()->find($selector, $locator);

            return empty($elem) || !$elem->isVisible();
        }, 90);
    }

    /**
     * @param string          $selector
     * @param string[]|string $locator
     *
     * @throws ElementNotFoundException
     */
    public function find($selector, $locator): NodeElement
    {
        $element = parent::find($selector, $locator);
        if ($element === null) {
            if (\is_array($locator)) {
                $locator = implode(' ', $locator);
            }
            throw new ElementNotFoundException($this->getDriver(), null, $selector, $locator);
        }

        return $element;
    }

    /**
     * @param string       $selector
     * @param array|string $locator
     */
    public function has($selector, $locator): bool
    {
        try {
            return parent::has($selector, $locator);
        } catch (ElementNotFoundException $e) {
            return false;
        }
    }
}
