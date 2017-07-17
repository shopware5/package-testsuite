<?php

namespace Shopware\Context;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Cocur\Slugify\Slugify;
use Dotenv\Dotenv;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Component\SpinTrait\SpinTrait;
use Shopware\Element\MultipleElement;

class SubContext extends PageObjectContext implements MinkAwareContext
{
    use SpinTrait;

    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var array
     */
    private $minkParameters;

    public function __construct()
    {
        $dotenv = new Dotenv(dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @return Mink
     */
    public function getMink()
    {
        return $this->mink;
    }

    /**
     * Sets parameters provided for Mink.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
    }

    /**
     * Returns specific mink parameter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getMinkParameter($name)
    {
        return isset($this->minkParameters[$name]) ? $this->minkParameters[$name] : null;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->mink->getSession();
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @param Page $page Parent page
     * @param string $elementName Name of the element
     * @param int $instance Instance of the element
     * @return MultipleElement
     */
    protected function getMultipleElement(Page $page, $elementName, $instance = 1)
    {
        /** @var MultipleElement $element */
        $element = $this->getElement($elementName);
        $element->setParent($page);

        if ($instance > 1) {
            $element = $element->setInstance($instance);
        }

        return $element;
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $text
     * @param int $sleep
     */
    protected function waitForText($text, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(text(), '$text')]");
            return $result != null && count($result) > 0;
        });
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $selector xpath selector
     * @param string $text
     * @param int $sleep
     * @param int $wait
     */
    protected function waitForTextInElement($selector, $text, $sleep = 2, $wait = 60)
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($text, $selector) {
            /** @var NodeElement $baseElement */
            $baseElement = $context->getSession()->getPage()->find('xpath', $selector);
            $result = $baseElement->findAll('xpath', "/descendant::*[contains(text(), '$text')]");
            return $result != null && count($result) > 0;
        }, $wait);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $text
     * @param int $sleep
     */
    protected function waitForTextNotPresent($text, $sleep = 2)
    {
        $this->waitForSelectorNotPresent('xpath', "//*[contains(text(), '$text')]", $sleep);
    }

    protected function waitForModalOverlayClosed()
    {
        $modalXPath = FrontendXpathBuilder::create()->child('div', ['~class' => 'js--overlay']);
        $this->waitForSelectorInvisible('xpath', $modalXPath);
    }

    /**
     * Checks via spin function if a locator is invisible on page, with sleep at the beginning (default 2)
     * @param string $selector css, xpath...
     * @param string $locator
     */
    protected function waitForSelectorInvisible($selector, $locator)
    {
        $this->spin(function (SubContext $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            return empty($elem) || !$elem->isVisible();
        }, 90);
    }

    /**
     * Checks via spin function if a locator is present on page, with sleep at the beginning (default 2)
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int $sleep
     * @return NodeElement
     */
    protected function waitForSelectorPresent($selector, $locator, $sleep = 2)
    {
        sleep($sleep);
        $elem = null;
        $this->spin(function (SubContext $context) use ($selector, $locator, &$elem) {
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
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int $sleep
     */
    protected function waitForSelectorNotPresent($selector, $locator, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            if ($elem === null) {
                return true;
            }
            return false;
        });
    }

    /**
     * Checks via a string exists
     * @param string $text
     * @param SubContext $context
     * @return bool
     */
    protected function checkIfThereIsText($text, SubContext $context)
    {
        $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(., '$text')]");
        return !empty($result);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $text
     * @param int $wait
     * @return bool
     */
    protected function waitIfThereIsText($text, $wait = 5)
    {
        return $this->spinWithNoException(function (SubContext $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, $wait);
    }

    /**
     * Checks via spin function if a string exists, returns false/true after $wait
     * @param string $text
     * @param int $wait
     * @return bool
     */
    protected function textExistsEventually($text, $wait = 60)
    {
        return $this->spinWithNoException(function (SubContext $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(., '$text')]");
            return $result != null && count($result) > 0;
        }, $wait);
    }

    protected function slugify($text, $separator = '')
    {
        $slugify = new Slugify();
        $slugify->addRule('@', 'at');
        return $slugify->slugify($text, $separator);
    }
}
