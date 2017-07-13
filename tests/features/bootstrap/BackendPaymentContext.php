<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Backend\BackendLogin;
use Shopware\Tests\Mink\Page\Backend\PaymentModule;

class BackendPaymentContext extends SubContext
{
    /**
     * @Given the following payment methods are activated:
     * @param TableNode $table
     */
    public function theFollowingPaymentMethodsAreActivated(TableNode $table)
    {
        $this->login();

        /** @var PaymentModule $page */
        $page = $this->getPage('PaymentModule');

        foreach ($table as $row) {
            $page->activatePaymentMethod($row['name']);
        }
    }

    /** Small helper method to log user into the backend */
    private function login()
    {
        /** @var BackendLogin $page */
        $page = $this->getPage('BackendLogin');
        $page->open();

        // See if we already are logged in
        if ($this->waitIfThereIsText('Marketing', 5)) {
            return;
        }

        $this->waitForText('Shopware Backend Login', 10);

        $page->login('demo', 'demo');
        $this->waitForText('Marketing');
    }
}