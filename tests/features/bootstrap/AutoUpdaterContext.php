<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Updater\AutoUpdaterIndex;

class AutoUpdaterContext extends SubContext
{
    private $testPath;
    private $testPathApache;

    public function __construct()
    {
        parent::__construct();
        $this->testPath = getenv('base_path');
        $this->testPathApache = getenv('base_path_apache');
    }
    /**
     * @Given the :label button should be disabled
     * @Given the :element element should be disabled
     */
    public function theButtonShouldBeDisabled($label)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->checkIfEnabled("xpath", $label, false);
    }

    /**
     * @When I click on the :tab tab
     */
    public function iClickOnTheTab($tab)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->clickOnTab($tab);
    }

    /**
     * @Given I confirm that I created a backup
     */
    public function iConfirmThatICreatedABackup()
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->tickConfirmationCheckbox();
    }

    /**
     * @Then the :label button should be enabled so that the update can be started
     * @Given the :element element should be enabled
     */
    public function theButtonShouldBeEnabledSoThatTheUpdateCanBeStarted($label)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->checkIfEnabled("xpath", $label, true);
    }

    /**
     * @When I click the :text element
     */
    public function iClickTheElement($text)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->clickOnElement($text);
    }

    /**
     * @When I click on :element to look at the update details
     */
    public function iClickOnPrepareTheUpdate($element)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->clickOnElement($element);
    }

    /**
     * @Given the :tab requirement is fullfilled
     */
    public function thePluginRequirementsAreFullfilled($tab)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->checkTabRequirementFullfilled($tab);
    }

    /**
     * @Given the listed requirements are fullfilled:
     */
    public function theListedRequirementsAreFullfilled1(TableNode $table)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');

        foreach ($table as $item) {
            $page->checkSystemRequirements($item,$this->testPathApache);
        }
    }

    /**
     * @Given I should see the link :linktext leading to :target after the update
     */
    public function iShouldSeeTheLinkLeadingTo($linktext, $target)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->checkIfShopIsAvailable($linktext, $target);
    }

    /**
     * @Given the auto update requirements are not met
     */
    public function theAutoUpdateRequirementsAreNotMet()
    {
        $this->setRequirementsFullfillment(false);
    }


    /**
     * @Given the listed requirements are not fullfilled
     */
    public function theRequirementIsNotFullfilled()
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');

        if ($page->checkSystemRequirementsUnfullfilled('Voraussetzungen') === false) {
            throw new \Exception('Requirement well met.');
        }
    }


    /**
     * @When I correct the auto update requirements
     */
    public function iCorrectTheAutoUpdateRequirements()
    {
        $this->setRequirementsFullfillment(true);
    }

    /**
     * @Given I refresh the window
     */
    public function iRefreshTheWindow()
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->refreshElement();
        sleep(5);
    }


    /**
     * @When I click the :text updater element
     */
    public function clickOnUpdaterElement($text)
    {
        /** @var AutoUpdaterIndex $page */
        $page = $this->getPage('AutoUpdaterIndex');
        $page->clickOnUpdaterElement($text);
    }

    /**
     * Fakes an unfullfilled requirement or undoes it
     * @param bool $meetRequirements Determines if the requirement should be met or not
     */
    private function setRequirementsFullfillment($meetRequirements)
    {
        if ($meetRequirements === false) {
            rename($this->testPath.'/recovery', $this->testPath.'/recovery-new');
            return;
        }
        rename($this->testPath.'/recovery-new', $this->testPath.'/recovery');
    }
}
