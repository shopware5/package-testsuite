<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Frontend\Account;

class FrontendAccountContext extends SubContext
{
    /**
     * @Given I register myself:
     * @param TableNode $table
     */
    public function iRegisterMyself(TableNode $table)
    {
        $data = $table->getHash();

        /** @var Account $page */
        $page = $this->getPage('Account');
        $page->open();

        // Already logged in
        if ($this->waitIfThereIsText("Willkommen")) {
            $page->logout();
            $this->waitForTextNotPresent("Willkommen");
        }

        $page->open();
        $page->register($data);
    }
}
