<?php
/**
 * Created by PhpStorm.
 * User: r.schwering
 * Date: 11.01.2017
 * Time: 07:53
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;

class AutoUpdaterContext implements Context
{
    /**
     * @Given the :label button should be disabled
     */
    public function theButtonShouldBeDisabled($label)
    {
        throw new PendingException();
    }

    /**
     * @When I click on the :title tab
     */
    public function iClickOnTheTab($title)
    {
        throw new PendingException();
    }

    /**
     * @Given the requirements are fullfilled
     */
    public function theRequirementsAreFullfilled()
    {
        throw new PendingException();
    }

    /**
     * @Given I confirm that I created a backup
     */
    public function iConfirmThatICreatedABackup()
    {
        throw new PendingException();
    }

    /**
     * @Then the :label button should be enabled so that the update can be started
     */
    public function theButtonShouldBeEnabledSoThatTheUpdateCanBeStarted($label)
    {
        throw new PendingException();
    }

    /**
     * @When I click the :text element
     */
    public function iClickTheElement($text)
    {
        throw new PendingException();
    }

    /**
     * @When I click on :element
     */
    public function iClickOn($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I log in in with user :username and password :password
     */
    public function iLogInInWithUserAndPassword($username, $password)
    {
        throw new PendingException();
    }
}