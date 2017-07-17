<?php

namespace Shopware\Context;

use Behat\Behat\Hook\Scope\ScenarioScope;

class GeneralContext extends SubContext
{
    /**
     * Clean up database after every feature
     *
     * @AfterFeature
     */
    public static function onAfterFeature()
    {
        self::cleanDatabase();
    }

    /**
     * Isolate scenarios tagged with 'isolated'
     *
     * @BeforeScenario
     * @AfterScenario
     * @param ScenarioScope $scope
     */
    public static function onAfterScenario(ScenarioScope $scope)
    {
        $tags = $scope->getScenario()->getTags();

        if(in_array('isolated', $tags)) {
            self::cleanDatabase();
        }
    }

    /**
     * Helper method that resets the database to a known, clean state
     */
    private static function cleanDatabase()
    {
        $dbDumpFile = __DIR__ . '/../clean_db.sql';

        if (!is_file($dbDumpFile)) {
            echo "Could not reset database - no clean state available. (Missing dump file)." . PHP_EOL;
            return;
        }

        echo "Resetting database to clean state..." . PHP_EOL;
        passthru(sprintf('mysql -u shopware -pshopware -h mysql shopware < %s', $dbDumpFile));
    }

    /**
     * @Given I am on the page :page
     * @When I go to the page :page
     * @param string $page
     */
    public function iAmOnThePage($page)
    {
        $page = $this->getPage($page);
        $page->open();
    }

    /**
     * @Then I should see :text eventually
     * @param string $text
     */
    public function iShouldSeeEventually($text)
    {
        $this->waitForText($text);
    }

    /**
     * @Then I should eventually not see :text
     * @param string $text
     */
    public function iShouldEventuallyNotSee($text)
    {
        $this->waitForTextNotPresent($text);
    }
}
