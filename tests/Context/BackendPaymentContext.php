<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\Backend;
use Shopware\Page\Backend\PaymentModule;

class BackendPaymentContext extends SubContext
{
    /**
     * @Given the following payment methods are activated:
     */
    public function theFollowingPaymentMethodsAreActivated(TableNode $table): void
    {
        $backendPage = $this->getValidPage(Backend::class);
        $backendPage->login();

        $page = $this->getValidPage(PaymentModule::class);

        foreach ($table as $row) {
            $page->activatePaymentMethod($row['name']);
        }
    }
}
