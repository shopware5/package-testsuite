<?php

namespace Shopware\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception;
use Behat\Mink\Session;
use Behat\Testwork\Tester\Result\TestResult;
use Shopware\Tests\Mink\SubContext;

class DebugContext extends SubContext
{
    /**
     * Save a screenshot and log additional info if a step has failed.
     *
     * @AfterStep
     * @param AfterStepScope $scope
     */
    public function onAfterStep(AfterStepScope $scope)
    {
        if (TestResult::FAILED === $scope->getTestResult()->getResultCode()) {
            $this->takeScreenshot();
            $this->logRequest();
        }
    }

    /**
     * Take a screenshot of the current Selenium Driver instance
     */
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
        $filename = $filename ?: sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), time(),
            uniqid('', true), 'png');
        $filepath = $filepath ? $filepath : (ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());

        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }

    /**
     * Log information about the current request
     */
    private function logRequest()
    {
        $session = $this->getSession();
        $log = sprintf('Current page: %d %s', $this->getStatusCode(), $session->getCurrentUrl()) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getRequestContentLogMessage($session);
        $this->saveLog($log, 'log');
    }

    /**
     * Save data into a logfile
     *
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
        } catch (Exception $exception) {
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
        } catch (Exception $exception) {
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
        } catch (Exception $exception) {
            return null;
        }
    }
}