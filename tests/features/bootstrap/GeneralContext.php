<?php

namespace Shopware\Tests\Mink;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class GeneralContext extends SubContext
{

    /**
     * @Given /^I am on the page "([^"]*)"$/
     * @When /^I go to the page "([^"]*")$/
     */
    public function iAmOnThePage($page)
    {
        /** @var Page $page */
        $page = $this->getPage($page);
        $page->open();
    }

    /**
     * @Then /^I should see "([^"]*)" eventually$/
     */
    public function iShouldSeeEventually($text)
    {
        $this->waitForText($text);
    }
}
