<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Shopware\Page\Backend\Backend;
use Shopware\Page\Backend\PaymentModule;

class BackendPaymentContext extends PageObjectContext
{
    /**
     * @Given the following payment methods are activated:
     */
    public function theFollowingPaymentMethodsAreActivated(TableNode $table)
    {
        /** @var Backend $backendPage */
        $backendPage = $this->getPage('Backend');
        $backendPage->login();

        /** @var PaymentModule $page */
        $page = $this->getPage('PaymentModule');

        foreach ($table as $row) {
            $page->activatePaymentMethod($row['name']);
        }
    }
}
