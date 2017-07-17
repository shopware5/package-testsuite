<?php

namespace Shopware\Context;

use Shopware\Tests\Mink\Page\Updater\UpdaterIndex;

class UpdaterContext extends SubContext
{
    /**
     * @var string
     */
    private $testPath;

    /**
     * UpdaterContext constructor override.
     */
    public function __construct()
    {
        parent::__construct();
        $this->testPath = getenv('base_path') . '/files/';
    }

    /**
     * @When I advance to the next updater page
     */
    public function iAdvanceToTheNextUpdaterPage()
    {
        /** @var UpdaterIndex $page */
        $page = $this->getPage('UpdaterIndex');
        $page->advance();
    }

    /**
     * @When I start the database migration
     */
    public function iClickToStartTheDatabaseMigration()
    {
        /** @var UpdaterIndex $page */
        $page = $this->getPage('UpdaterIndex');
        $page->clickOnDbStart('startDbMigrationButton');
    }

    /**
     * @When I have unused files in my installation
     */
    public function iHaveUnusedFilesInMyInstallation()
    {
        $page = $this->getPage('UpdaterIndex');
        $entry = $page->find('css', 'td');

        if ($entry) {
            return;
        }
    }

    /**
     * @When the cleanup will be finished and the loading indicator disappears
     */
    public function theCleanupWillBeFinished()
    {
        /** @var UpdaterIndex $page */
        $page = $this->getPage('UpdaterIndex');
        $page->finishCleanup();
    }

    /**
     * @Given I should see the reminder :hint to remove the update-assets folder
     * @param string $hint
     */
    public function iShouldSeeTheReminderToRemoveTheUpdateAssetsFolder($hint)
    {
        /** @var UpdaterIndex $page */
        $page = $this->getPage('UpdaterIndex');
        $page->handleUpdateAssets($hint);
    }

    /**
     * @Given the update requirements are met
     */
    public function theUpdateRequirementsAreMet()
    {
        $this->setRequirementsFulfillment(true);
    }

    /**
     * @Given the update requirements are not met
     */
    public function theUpdateRequirementsAreNotMet()
    {
        $this->setRequirementsFulfillment(false);
    }

    /**
     * Sets the access privileges of a directory according to the situation to simulate system requirements
     * @param bool $meetRequirements
     */
    private function setRequirementsFulfillment($meetRequirements)
    {
        if ($meetRequirements === false) {
            chmod($this->testPath, 0444);
            return;
        }
        chmod($this->testPath, 0777);
    }

    /**
     * @When I correct the requirements
     */
    public function iCorrectTheRequirements()
    {
        $this->setRequirementsFulfillment(true);
    }

    /**
     * @When I advance to the next step via :stepName
     * @param $stepName
     */
    public function iAdvanceToTheNextRequirementsStep($stepName)
    {
        /** @var UpdaterIndex $page */
        $page = $this->getPage('UpdaterIndex');
        $page->advanceToStep($stepName);
    }
}
