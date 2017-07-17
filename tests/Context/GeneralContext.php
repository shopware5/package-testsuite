<?php

namespace Shopware\Context;

class GeneralContext extends SubContext
{
    /**
     * @Given I am on the page :page
     * @When I go to the page :page
     * @param string $page
     */
    public function iAmOnThePage($page)
    {
        $page = $this->getPage($page);
        $page->open();
    }

    /**
     * @Then I should see :text eventually
     * @param string $text
     */
    public function iShouldSeeEventually($text)
    {
        $this->waitForText($text);
    }

    /**
     * @Then I should eventually not see :text
     * @param string $text
     */
    public function iShouldEventuallyNotSee($text)
    {
        $this->waitForTextNotPresent($text);
    }
}
