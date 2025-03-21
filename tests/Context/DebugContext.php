<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception;
use Behat\Mink\Session;
use Behat\Testwork\Tester\Result\TestResult;
use RuntimeException;

class DebugContext extends SubContext
{
    /**
     * Save a screenshot and log additional info if a step has failed.
     *
     * @AfterStep
     */
    public function onAfterStep(AfterStepScope $scope): void
    {
        if ($scope->getTestResult()->getResultCode() === TestResult::FAILED) {
            $this->takeScreenshot();
            $this->logRequest();
        }
    }

    /**
     * Save a screenshot of the current window to the file system.
     *
     * @param string|null $filename Desired filename, defaults to
     *                              <browser_name>_<ISO 8601 date>_<randomId>.png
     * @param string|null $filepath Desired filepath, defaults to
     *                              upload_tmp_dir, falls back to sys_get_temp_dir()
     */
    public function saveScreenshot(?string $filename = null, ?string $filepath = null): void
    {
        $filename = $filename ?: \sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), time(),
            uniqid('', true), 'png');
        $filepath = $filepath ?: (\ini_get('upload_tmp_dir') ?: sys_get_temp_dir());

        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }

    /**
     * Take a screenshot of the current Selenium Driver instance
     */
    private function takeScreenshot(): void
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $filePath = \dirname(__FILE__, 2) . '/logs/mink';

        $this->saveScreenshot(null, $filePath);
    }

    /**
     * Log information about the current request
     */
    private function logRequest(): void
    {
        $session = $this->getSession();
        $log = \sprintf('Current page: %d %s', $this->getStatusCode(), $session->getCurrentUrl()) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getRequestContentLogMessage($session);
        $this->saveLog($log, 'log');
    }

    /**
     * Save data into a logfile
     */
    private function saveLog(string $content, string $type): void
    {
        $logDir = \dirname(__FILE__, 2) . '/logs/mink';

        $currentDateAsString = date('YmdHis');

        $path = \sprintf('%s/behat-%s.%s', $logDir, $currentDateAsString, $type);
        if (!file_put_contents($path, $content)) {
            throw new RuntimeException(\sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    private function getStatusCode(): ?int
    {
        try {
            return $this->getSession()->getStatusCode();
        } catch (Exception $exception) {
            return null;
        }
    }

    private function getResponseHeadersLogMessage(Session $session): ?string
    {
        try {
            return "Response headers:\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (Exception $exception) {
            return null;
        }
    }

    private function getRequestContentLogMessage(Session $session): ?string
    {
        try {
            return "Response content:\n" . $session->getPage()->getContent() . "\n";
        } catch (Exception $exception) {
            return null;
        }
    }
}
