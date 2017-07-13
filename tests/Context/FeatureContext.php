<?php

namespace Shopware\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Shopware\Tests\Mink\SubContext;

class FeatureContext extends SubContext
{
    /**
     * Maximize browser window before execution
     *
     * @BeforeStep
     */
    public function onBeforeStep()
    {
        $driver = $this->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $driver->maximizeWindow();
            $this->getSession()->resizeWindow(1920, 1080, 'current');
        }
    }
}
