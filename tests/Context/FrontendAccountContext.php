<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Account;

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
        $alreadyLoggedIn = $this->waitIfThereIsText("Willkommen");
        if ($alreadyLoggedIn) {
            $page->logout();
            $this->waitForTextNotPresent("Willkommen");
        }
        $page->open();
        $page->register($data);
    }
}
