<?php

declare(strict_types=1);

namespace Shopware\Context;

use Shopware\Page\Updater\UpdaterIndex;

class UpdaterContext extends SubContext
{
    private string $testPath;

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
    public function iAdvanceToTheNextUpdaterPage(): void
    {
        $page = $this->getValidPage(UpdaterIndex::class);
        $page->advance();
    }

    /**
     * @When I start the database migration
     */
    public function iClickToStartTheDatabaseMigration(): void
    {
        $page = $this->getValidPage(UpdaterIndex::class);
        $page->clickOnDbStart();
    }

    /**
     * @When I have unused files in my installation
     */
    public function iHaveUnusedFilesInMyInstallation(): void
    {
        $this->getValidPage(UpdaterIndex::class)->find('css', 'td');
    }

    /**
     * @When the cleanup will be finished and the loading indicator disappears
     */
    public function theCleanupWillBeFinished(): void
    {
        $page = $this->getValidPage(UpdaterIndex::class);
        $page->finishCleanup();
    }

    /**
     * @Given I should see the reminder :hint to remove the update-assets folder
     */
    public function iShouldSeeTheReminderToRemoveTheUpdateAssetsFolder(string $hint): void
    {
        $page = $this->getValidPage(UpdaterIndex::class);
        $page->handleUpdateAssets($hint);
    }

    /**
     * @Given the update requirements are met
     */
    public function theUpdateRequirementsAreMet(): void
    {
        $this->setRequirementsFulfillment(true);
    }

    /**
     * @Given the update requirements are not met
     */
    public function theUpdateRequirementsAreNotMet(): void
    {
        $this->setRequirementsFulfillment(false);
    }

    /**
     * @When I correct the requirements
     */
    public function iCorrectTheRequirements(): void
    {
        $this->setRequirementsFulfillment(true);
    }

    /**
     * @When I advance to the next step via :stepName
     */
    public function iAdvanceToTheNextRequirementsStep(string $stepName): void
    {
        $page = $this->getValidPage(UpdaterIndex::class);
        $page->advanceToStep($stepName);
    }

    /**
     * Sets the access privileges of a directory according to the situation to simulate system requirements
     */
    private function setRequirementsFulfillment(bool $meetRequirements): void
    {
        if ($meetRequirements === false) {
            chmod($this->testPath, 0444);

            return;
        }
        chmod($this->testPath, 0777);
    }
}
