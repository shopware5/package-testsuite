<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Exception;
use Shopware\Page\Backend\CustomerModule;

class BackendCustomerContext extends SubContext
{
    /**
     * @Then I might need to close the welcome wizard
     */
    public function iMightNeedToCloseTheWelcomeWizard(): void
    {
        $this->getModulePage()->skipIntroWizardIfNecessary();
    }

    /**
     * @When I fill out the new customer form:
     */
    public function fillNewCustomerForm(TableNode $table): void
    {
        $this->getModulePage()->fillNewCustomerFormWith($table->getHash());
    }

    /**
     * @When I click the edit icon on customer :firstname
     *
     * @throws Exception
     */
    public function iClickTheEditIconOnCustomer(string $name): void
    {
        $this->getModulePage()->openEditFormForCustomer($name);
    }

    /**
     * @When I change the following information:
     */
    public function iChangeTheFollowingInformation(TableNode $table): void
    {
        $this->getModulePage()->fillEditCustomerFormWith($table->getHash());
    }

    /**
     * @When I click the delete icon on customer :firstname
     */
    public function iClickTheDeleteIconOnCustomer(string $firstname): void
    {
        $this->getModulePage()->clickDeleteIconForCustomer($firstname);
    }

    private function getModulePage(): CustomerModule
    {
        return $this->getValidPage(CustomerModule::class);
    }
}
