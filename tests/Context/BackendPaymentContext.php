<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\BackendLogin;
use Shopware\Page\Backend\PaymentModule;

class BackendPaymentContext extends SubContext
{
    /**
     * @Given the following payment methods are activated:
     * @param TableNode $table
     */
    public function theFollowingPaymentMethodsAreActivated(TableNode $table)
    {
        /** @var BackendLogin $page */
        $page = $this->getPage('BackendLogin');
        $page->login();

        /** @var PaymentModule $page */
        $page = $this->getPage('PaymentModule');

        foreach ($table as $row) {
            $page->activatePaymentMethod($row['name']);
        }
    }
}