<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Tester\Result\TestResult;
use Shopware\Tests\Mink\Page\Updater\AutoUpdaterIndex;

class AutoUpdaterContext extends SubContext
{
    /** @var string Path to shopware installation */
    private $testPath;

    private $testPathApache;

    public function __construct()
    {
        parent::__construct();
        $this->testPath = getenv('base_path');
        $this->testPathApache = getenv('base_path_apache');
    }

    /**
     * @AfterScenario
     */
    public function onAfterScenario()
    {
        $this->repairRequirements();
    }

    /**
     * @Given the auto update requirements are not met
     */
    public function theAutoUpdateRequirementsAreNotMet()
    {
        $this->breakRequirements();
    }

    /**
     * @When I correct the auto update requirements
     */
    public function iCorrectTheAutoUpdateRequirements()
    {
        $this->repairRequirements();
    }

    /**
     * Break the requirements needed by the auto-updater
     */
    private function breakRequirements()
    {
        if (!$this->areRequirementsBroken()) {
            rename($this->testPath . '/recovery', $this->testPath . '/recovery_broken');
        }
    }

    /**
     * Repair the requirements needed by the auto-updater
     */
    private function repairRequirements()
    {
        if ($this->areRequirementsBroken()) {
            rename($this->testPath . '/recovery_broken', $this->testPath . '/recovery');
        }
    }

    /**
     * @return bool
     */
    private function areRequirementsBroken()
    {
        return !is_dir($this->testPath . '/recovery') && is_dir($this->testPath . '/recovery_broken');
    }

    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function waitToDebugInBrowserOnStepErrorHook(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() == TestResult::FAILED) {
            echo PHP_EOL . "PAUSING ON FAIL" . PHP_EOL;
            $this->getSession()->wait(10000000000);
        }
    }

    /**
     * @Given the :label button should be disabled
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
            $page->checkSystemRequirements($item, $this->testPathApache);
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
}
