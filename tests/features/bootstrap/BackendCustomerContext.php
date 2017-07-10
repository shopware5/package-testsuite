<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Backend\CustomerModule;

class BackendCustomerContext extends SubContext
{
    /**
     * @When I fill out the new customer form:
     */
    public function fillNewCustomerForm(TableNode $table)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $data = $table->getHash();

        $page->fillNewCustomerFormWith($data);
    }

    /**
     * @When I click the edit icon on customer :firstname
     */
    public function iClickTheEditIconOnCustomer($firstname)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $page->openEditFormForCustomer($firstname);
    }

    /**
     * @When I change the following information:
     */
    public function iChangeTheFollowingInformation(TableNode $table)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $data = $table->getHash();

        $page->fillEditCustomerFormWith($data);
    }

    /**
     * @When I click the delete icon on customer :firstname
     */
    public function iClickTheDeleteIconOnCustomer($firstname)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $page->clickDeleteIconForCustomer($firstname);
    }
}