<?php

namespace Shopware\Page;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\SpinTrait\SpinTrait;
use Shopware\Component\XpathBuilder\BaseXpathBuilder;

class ContextAwarePage extends Page
{
    use SpinTrait;

    /**
     * @param string $message
     *
     * @throws \UnexpectedValueException
     */
    protected function assertNotNull($element, $message)
    {
        if ($element === null) {
            throw new \UnexpectedValueException('Failed Assertion: Element of type ' . \get_class($element) . 'is null - ' . $message);
        }
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     *
     * @param string $text
     * @param int    $sleep
     *
     * @throws \Exception
     */
    protected function waitForText($text, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(text(), '$text')]");

            return $result != null && \count($result) > 0;
        });
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     *
     * @param string $text
     * @param int    $wait
     *
     * @return bool
     */
    protected function waitIfThereIsText($text, $wait = 5)
    {
        return $this->spinWithNoException(function (self $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, $wait);
    }

    /**
     * Checks via a string exists
     *
     * @param string $text
     *
     * @return bool
     */
    protected function checkIfThereIsText($text, ContextAwarePage $context)
    {
        $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(., '$text')]");

        return !empty($result);
    }

    /**
     * Checks via spin function if a locator is present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int    $sleep
     *
     * @return NodeElement
     */
    protected function waitIfSelectorPresent($selector, $locator, $sleep = 2)
    {
        sleep($sleep);
        $elem = null;
        $this->spinWithNoException(function (ContextAwarePage $context) use ($selector, $locator, &$elem) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);

            return !($elem === null);
        });

        return $elem;
    }

    /**
     * Checks via spin function if a locator is present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int    $sleep
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function waitForSelectorPresent($selector, $locator, $sleep = 2)
    {
        sleep($sleep);
        $elem = null;
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator, &$elem) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            if ($elem === null) {
                return false;
            }

            return true;
        });

        return $elem;
    }

    /**
     * Checks via spin function if a locator is not present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int    $sleep
     *
     * @throws \Exception
     */
    protected function waitForSelectorNotPresent($selector, $locator, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
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
     * @param int $sleep
     *
     * @throws \Exception
     */
    protected function waitForXpathElementPresent($xpath, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($xpath) {
            /** @var NodeElement $elem */
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
     * @param string $text
     * @param int    $sleep
     *
     * @throws \Exception
     */
    protected function waitForTextNotPresent($text, $sleep = 2)
    {
        $this->waitForSelectorNotPresent('xpath', "//*[contains(text(), '$text')]", $sleep);
    }

    /**
     * @param NodeElement $parent
     * @param string      $xPath
     * @param string      $class
     *
     * @throws \Exception
     */
    protected function waitForClassNotPresent($parent, $xPath, $class)
    {
        $this->spin(function () use ($parent, $xPath, $class) {
            $element = $parent->find('xpath', $xPath);
            if ($element->hasClass($class)) {
                return false;
            }

            return true;
        });
    }

    protected function waitForModalOverlayClosed()
    {
        $modalXPath = BaseXpathBuilder::create()->child('div', ['~class' => 'js--overlay']);
        $this->waitForSelectorInvisible('xpath', $modalXPath);
    }

    /**
     * Checks via spin function if element is clickable, then clicks it, with sleep at the beginning (default 2)
     *
     * @throws \Exception
     */
    protected function clickOnElementWhenReady(NodeElement $elem)
    {
        $this->spin(function () use ($elem) {
            try {
                $elem->click();

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }, 90);
    }

    /**
     * Checks via spin function if a locator is visible on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     * @param string $locator
     *
     * @throws \Exception
     */
    protected function waitForSelectorVisible($selector, $locator)
    {
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);

            return !empty($elem) || $elem->isVisible();
        }, 90);
    }

    /**
     * Checks via spin function if a locator is invisible on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int    $wait
     */
    protected function waitForSelectorInvisible($selector, $locator, $wait = 2)
    {
        sleep($wait);
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);

            return empty($elem) || !$elem->isVisible();
        }, 90);
    }
}
