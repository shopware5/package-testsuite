<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;

class GeneralContext extends SubContext
{
    /**
     * Clean up database after every feature
     *
     * @AfterFeature
     */
    public static function onAfterFeature(): void
    {
        self::cleanDatabase();
        self::clearCache();
    }

    /**
     * Isolate scenarios tagged with 'isolated'
     *
     * @BeforeScenario
     * @AfterScenario
     */
    public static function onAfterScenario(ScenarioScope $scope): void
    {
        $tags = $scope->getScenario()->getTags();

        if (\in_array('isolated', $tags)) {
            self::cleanDatabase();
            self::clearCache();
        }
    }

    /**
     * Maximize browser window before execution
     *
     * @BeforeScenario
     */
    public function onBeforeScenario(): void
    {
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver) {
            $driver->resizeWindow(1920, 1080);
        }
    }

    /**
     * @Given I wait for :amount seconds
     */
    public function iWaitForSeconds($amount): void
    {
        sleep((int) $amount);
    }

    /**
     * @Given /^I scroll down "([^"]*)" px$/
     */
    public function iScrollDown($pixels): void
    {
        $this->getSession()->executeScript(sprintf('window.scroll(0, %s)', $pixels));
    }

    /**
     * @Given I am on the page :pageName
     * @When I go to the page :pageName
     */
    public function iAmOnThePage(string $pageName): void
    {
        $page = $this->getPage($pageName);
        $page->open();
    }

    /**
     * @Then I should see :text eventually
     */
    public function iShouldSeeEventually(string $text): void
    {
        $this->waitForText($text);
    }

    /**
     * @Then I should eventually not see :text
     */
    public function iShouldEventuallyNotSee(string $text): void
    {
        $this->waitForTextNotPresent($text);
    }

    /**
     * Helper method that resets the database to a known, clean state
     */
    private static function cleanDatabase(): void
    {
        $dbDumpFile = __DIR__ . '/../clean_db.sql';

        if (!is_file($dbDumpFile)) {
            echo 'Could not reset database - no clean state available. (Missing dump file).' . PHP_EOL;

            return;
        }

        echo 'Resetting database to clean state...' . PHP_EOL;
        passthru(sprintf('mysql -u shopware -pshopware -h mysql shopware < %s', $dbDumpFile));
    }

    /**
     * Helper method that clears the shopware cache
     */
    private static function clearCache(): void
    {
        echo 'Clearing Shopware cache...' . PHP_EOL;
        $swConsole = getenv('base_path') . '/bin/console';
        shell_exec('php ' . $swConsole . ' sw:cache:clear');
    }
}
