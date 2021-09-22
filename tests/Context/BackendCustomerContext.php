<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Shopware\Page\Backend\CustomerModule;

class BackendCustomerContext extends PageObjectContext
{
    /**
     * @Then I might need to close the welcome wizard
     */
    public function iMightNeedToCloseTheWelcomeWizard()
    {
        $this->getModulePage()->skipIntroWizardIfNecessary();
    }

    /**
     * @When I fill out the new customer form:
     */
    public function fillNewCustomerForm(TableNode $table)
    {
        $this->getModulePage()->fillNewCustomerFormWith($table->getHash());
    }

    /**
     * @When I click the edit icon on customer :firstname
     *
     * @param string $name
     *
     * @throws \Exception
     */
    public function iClickTheEditIconOnCustomer($name)
    {
        $this->getModulePage()->openEditFormForCustomer($name);
    }

    /**
     * @When I change the following information:
     */
    public function iChangeTheFollowingInformation(TableNode $table)
    {
        $this->getModulePage()->fillEditCustomerFormWith($table->getHash());
    }

    /**
     * @When I click the delete icon on customer :firstname
     *
     * @param string $firstname
     */
    public function iClickTheDeleteIconOnCustomer($firstname)
    {
        $this->getModulePage()->clickDeleteIconForCustomer($firstname);
    }

    /**
     * @return CustomerModule
     */
    private function getModulePage()
    {
        /** @var CustomerModule $page */
        $page = $this->getPage('CustomerModule');

        return $page;
    }
}
