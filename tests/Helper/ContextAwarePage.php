<?php

namespace Shopware\Helper;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Helper\XpathBuilder;

class ContextAwarePage extends Page
{
    use SpinTrait;

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $text
     * @param int $sleep
     */
    protected function waitForText($text, $sleep = 2)
    {
        sleep($sleep);
        $this->spin(function (ContextAwarePage $context) use ($text) {
            $result = $context->getSession()->getPage()->findAll('xpath', "//*[contains(text(), '$text')]");
            return $result != null && count($result) > 0;
        });
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
     * @param string $selector css, xpath...
     * @param string $locator
     * @param int $sleep
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
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     * @param string $text
     * @param int $sleep
     */
    protected function waitForTextNotPresent($text, $sleep = 2)
    {
        $this->waitForSelectorNotPresent('xpath', "//*[contains(text(), '$text')]", $sleep);
    }

    /**
     * For use in backend forms, sets the dropdown value in an editor window
     * @param NodeElement $editor The NodeElement of the editor window
     * @param string $pebble The Xpath for the dropdown pebble
     * @param string $action The action name of the fake ExtJS dropdown list
     * @param string $option The name of the entry to select
     */
    protected function setBackendDropdownValue($editor, $pebble, $action, $option)
    {
        $xp = new XpathBuilder();

        /** @var NodeElement $calculationSelectorPebble */
        $typeSelectorPebble = $editor->find('xpath', $pebble);
        $typeSelectorPebble->click();

        /** @var NodeElement $calculationList */
        $calculationOption = $this->find('xpath', $xp->xDropdown($action)->liWithText($option)->get());
        $calculationOption->click();
    }

    protected function waitForModalOverlayClosed()
    {
        $xp = new XpathBuilder();
        $modalXPath = $xp
            ->div(['~class' => ['js--overlay']])
            ->get();
        $this->waitForSelectorInvisible('xpath', $modalXPath);
    }

    /**
     * Checks via spin function if a locator is invisible on page, with sleep at the beginning (default 2)
     * @param string $selector css, xpath...
     * @param string $locator
     */
    protected function waitForSelectorInvisible($selector, $locator)
    {
        $this->spin(function (ContextAwarePage $context) use ($selector, $locator) {
            /** @var NodeElement $elem */
            $elem = $context->getSession()->getPage()->find($selector, $locator);
            return empty($elem) || !$elem->isVisible();
        }, 90);
    }

    /**
     * @param NodeElement $parent
     * @param string $label
     * @param string $action
     * @param string $optionText
     */
    protected function selectFromXDropdown(NodeElement $parent, $label, $action, $optionText)
    {
        $xp =new XpathBuilder();
        $pebbleXpath = $xp->getXSelectorPebbleForLabel($label);
        $pebble = $parent->find('xpath', $pebbleXpath);
        if(empty($pebble)){
            echo "\n";
            print_r([$parent->getXpath(),$pebbleXpath]);
            echo "\n";
        }
        $pebble->click();

        $dropdownOption = $this->waitForSelectorPresent('xpath', $xp->xDropdown($action, $optionText)->get());
        $dropdownOption->click();
    }
}