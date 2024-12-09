<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Cocur\Slugify\Slugify;
use Dotenv\Dotenv;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\SpinTrait\SpinTrait;
use Shopware\Context\Exception\PageNotDefinedException;

class SubContext extends PageObjectContext implements MinkAwareContext
{
    use SpinTrait;

    private Mink $mink;

    private array $minkParameters;

    public function __construct()
    {
        $dotenv = Dotenv::createUnsafeImmutable(\dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink): void
    {
        $this->mink = $mink;
    }

    public function getMink(): Mink
    {
        return $this->mink;
    }

    /**
     * Sets parameters provided for Mink.
     */
    public function setMinkParameters(array $parameters): void
    {
        $this->minkParameters = $parameters;
    }

    /**
     * Returns specific mink parameter.
     */
    public function getMinkParameter(string $name): ?string
    {
        return $this->minkParameters[$name] ?? null;
    }

    public function getSession(): Session
    {
        if (!$this->mink->getSession()->isStarted()) {
            $this->mink->getSession()->start();
        }

        return $this->mink->getSession();
    }

    public function getDriver(): DriverInterface
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @template TPage of Page
     *
     * @param class-string<TPage> $pageClass
     *
     * @return TPage
     */
    public function getValidPage(string $pageClass): Page
    {
        $page = $this->getPage($pageClass);
        if (!$page instanceof $pageClass) {
            throw new PageNotDefinedException($pageClass);
        }

        return $page;
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitForText(string $text, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(text(), '$text')]");

            return !empty($result);
        });
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     *
     * @param string $selector xpath selector
     */
    protected function waitForTextInElement(string $selector, string $text, int $sleep = 2, int $wait = 60): void
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($text, $selector) {
            $baseElement = $context->getSession()->getPage()->find('xpath', $selector);
            if (!$baseElement instanceof NodeElement) {
                throw new ElementNotFoundException($this->getDriver(), null, $selector);
            }
            $result = $baseElement->findAll('xpath', "/descendant::*[contains(text(), '$text')]");

            return \count($result) > 0;
        }, $wait);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitForTextNotPresent(string $text, int $sleep = 2): void
    {
        $this->waitForSelectorNotPresent('xpath', "//*[contains(text(), '$text')]", $sleep);
    }

    /**
     * Checks via spin function if a locator is present on page, with sleep at the beginning (default 2)
     *
     * @param string $selector css, xpath...
     */
    protected function waitForSelectorPresent(string $selector, string $locator, int $sleep = 2): NodeElement
    {
        sleep($sleep);
        $elem = null;
        $this->spin(function (SubContext $context) use ($selector, $locator, &$elem) {
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
     */
    protected function waitForSelectorNotPresent(string $selector, string $locator, int $sleep = 2): void
    {
        sleep($sleep);
        $this->spin(function (SubContext $context) use ($selector, $locator) {
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            if ($elem === null) {
                return true;
            }

            return false;
        });
    }

    /**
     * Checks via a string exists
     */
    protected function checkIfThereIsText(string $text, SubContext $context): bool
    {
        $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(., '$text')]");

        return !empty($result);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitIfThereIsText(string $text, int $wait = 5): bool
    {
        return $this->spinWithNoException(function (SubContext $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, $wait);
    }

    protected function slugify(string $text, string $separator = ''): string
    {
        $slugify = new Slugify();
        $slugify->addRule('@', 'at');

        return $slugify->slugify($text, $separator);
    }
}
