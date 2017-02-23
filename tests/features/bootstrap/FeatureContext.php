<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception as MinkException;
use Behat\Mink\Session;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends SubContext implements SnippetAcceptingContext
{

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Take screenshot when step fails. Works only with Selenium2Driver.
     *
     * @AfterStep
     * @param AfterStepScope $scope
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $scope)
    {
        if (TestResult::FAILED === $scope->getTestResult()->getResultCode()) {
            $this->takeScreenshot();
            $this->logRequest();
        }
    }

    /**
     * @BeforeStep
     */
    public function beforeStepConfig()
    {
        $driver = $this->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $driver->maximizeWindow();
        }
    }

    private function logRequest()
    {
        $session = $this->getSession();
        $log = sprintf('Current page: %d %s', $this->getStatusCode(), $session->getCurrentUrl()) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getRequestContentLogMessage($session);
        $this->saveLog($log, 'log');
    }

    /**
     * @param string $content
     * @param string $type
     */
    private function saveLog($content, $type)
    {
        $logDir = dirname(dirname(dirname(__FILE__))) . '/logs/mink';

        $currentDateAsString = date('YmdHis');

        $path = sprintf("%s/behat-%s.%s", $logDir, $currentDateAsString, $type);
        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException(sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    /**
     * @return int|null
     */
    private function getStatusCode()
    {
        try {
            return $this->getSession()->getStatusCode();
        } catch (MinkException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getResponseHeadersLogMessage(Session $session)
    {
        try {
            return 'Response headers:' . "\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getRequestContentLogMessage(Session $session)
    {
        try {
            return 'Response content:' . "\n" . $session->getPage()->getContent() . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    private function takeScreenshot()
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $filePath = dirname(dirname(dirname(__FILE__))) . '/logs/mink';

        $this->saveScreenshot(null, $filePath);
    }

    /**
     * Save a screenshot of the current window to the file system.
     *
     * @param string $filename Desired filename, defaults to
     *                         <browser_name>_<ISO 8601 date>_<randomId>.png
     * @param string $filepath Desired filepath, defaults to
     *                         upload_tmp_dir, falls back to sys_get_temp_dir()
     */
    public function saveScreenshot($filename = null, $filepath = null)
    {
        // Under Cygwin, uniqid with more_entropy must be set to true.
        // No effect in other environments.
        $filename = $filename ?: sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), time(),
            uniqid('', true), 'png');
        $filepath = $filepath ? $filepath : (ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());
        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }

    /**
     * @Given I am in subshop with URL :url
     * @param $url
     */
    public function iAmInSubshopWithURL($url)
    {
        if (substr($url, 0, 4) === "http") {
            $this->setMinkParameters([
                'base_url' => $url
            ]);
            return;
        }
        $baseUrl = $this->getMinkParameter('base_url');
        $this->setMinkParameters([
            'base_url' => rtrim($baseUrl, "/") . "/" . ltrim($url, "/")
        ]);
    }
}
