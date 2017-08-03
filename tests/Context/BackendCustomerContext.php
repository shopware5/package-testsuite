<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\CustomerModule;

class BackendCustomerContext extends SubContext
{
    /**
     * @Then I might need to close the welcome wizard
     */
    public function iMightNeedToCloseTheWelcomeWizard()
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');

        if($this->waitIfThereIsText('Überspringen')) {
            $page->skipIntroWizard();
        }
    }

    /**
     * @When I fill out the new customer form:
     * @param TableNode $table
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
     * @param string $firstname
     */
    public function iClickTheEditIconOnCustomer($firstname)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $page->openEditFormForCustomer($firstname);
    }

    /**
     * @When I change the following information:
     * @param TableNode $table
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
     * @param string $firstname
     */
    public function iClickTheDeleteIconOnCustomer($firstname)
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');
        $page->clickDeleteIconForCustomer($firstname);
    }
}