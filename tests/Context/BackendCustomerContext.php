<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\BackendModule;
use Shopware\Page\Backend\CustomerModule;

class BackendCustomerContext extends SubContext
{
    /**
     * @Then I might need to close the welcome wizard
     * @throws \Exception
     */
    public function iMightNeedToCloseTheWelcomeWizard()
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');

        if ($this->waitIfThereIsText('Überspringen')) {
            $page->skipIntroWizard();
        }
    }

    /**
     * @When I fill out the new customer form:
     * @param TableNode $table
     * @throws \Exception
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
     * @param string $name
     * @throws \Exception
     */
    public function iClickTheEditIconOnCustomer($name)
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        $page->clickEntryIconByName($name, 'sprite-pencil');
    }

    /**
     * @When I change the following information:
     * @param TableNode $table
     * @throws \Exception
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
     * @throws \Exception
     */
    public function iClickTheDeleteIconOnCustomer($firstname)
    {
        /** @var BackendModule $page */
        $page = $this->getPage('BackendModule');
        $page->clickEntryIconByName($firstname, 'sprite-minus-circle-frame');
    }
}
