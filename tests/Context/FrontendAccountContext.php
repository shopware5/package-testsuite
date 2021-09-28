<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Frontend\Account;

class FrontendAccountContext extends SubContext
{
    /**
     * @Given I register myself:
     */
    public function iRegisterMyself(TableNode $table): void
    {
        $data = $table->getHash();

        $page = $this->getValidPage('Account', Account::class);
        $page->open();

        // Already logged in
        if ($this->waitIfThereIsText('Willkommen')) {
            $page->logout();
            $this->waitForTextNotPresent('Willkommen');
        }

        $page->open();
        $page->register($data);
    }
}
