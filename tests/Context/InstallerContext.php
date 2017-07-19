<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Installer\InstallerIndex;

class InstallerContext extends SubContext
{

    /**
     * @When I advance to the next installer page
     */
    public function iAdvanceToTheNextInstallerPage()
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->advance();
    }

    /**
     * @Then I should see the list of required files and folders:
     * @Then I should see the checks for my system:
     * @param TableNode $table
     */
    public function iShouldSeeText(TableNode $table)
    {
        $data = $table->getHash();

        foreach ($data as $text) {
            $this->checkIfThereIsText($text['text'], $this);
        }
    }

    /**
     * @When I check the license checkbox to agree to the terms
     */
    public function iCheckTheCheckbox()
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->tickCheckbox('licenseCheckbox');
    }

    /**
     * @Then the following form fields must be required:
     * @param TableNode $table
     */
    public function theFollowingFormFieldsMustBeRequired(TableNode $table)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $data = $table->getHash();

        foreach ($data as $field) {
            $page->checkRequiredFields($field);
        }
    }

    /**
     * @When I fill the form:
     * @param TableNode $table
     */
    public function iFillTheForm(TableNode $table)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $data = $table->getHash();

        $page->fillInAndSubmitForm($data);
    }

    /**
     * @When I click on :text on the installer page to start the database update
     * @param string $text
     */
    public function iClickOn($text)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->clickOnElementWithText($text);
    }

    /**
     * @When I go back to the previous installer page
     */
    public function iGoBackToThePreviousInstallerPage()
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->returnToPreviousDbPage();
    }

    /**
     * @When I choose the radio field with value :value
     * @param string $value
     */
    public function iChooseTheRadioFieldWithValue($value)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->tickRadioButtonOption($value);
    }

    /**
     * @Given I should see the link :linktext leading to :target
     * @param string $linktext
     * @param string $target
     */
    public function iShouldSeeTheLinkLeadingTo($linktext, $target)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->checkIfShopIsAvailable($linktext, $target);
    }

    /**
     * @Then I should see :text after import is finished
     */
    public function iShouldSeeAfterImportIsFinished($text)
    {
        $builder = new BackendXpathBuilder();
        $this->waitForTextInElement($builder->child('div', ['@class' => 'counter-container'])->getXpath(), $text, 0, 120);
    }

    /**
     * Just for Shopware 5.2
     *
     * @Then I should see :text after the database import has finished
     * @param string $text
     */
    public function iShouldSeeTextAfterTheDatabaseImportHasFinished($text)
    {
        $builder = new BackendXpathBuilder();
        $this->waitForTextInElement($builder->child('div', ['@class' => 'counter-text'])->getXpath(), $text, 0, 120);
    }

    /**
     * @Then the :field field should get activated so that I am able to enter the license
     * @param string $field
     */
    public function theFieldShouldGetActivated($field)
    {
        usleep(250000);
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->checkIfDisabled('css', $field);
    }

    /**
     * @Given I click :text to skip the next installer page
     * @param string $text
     */
    public function iSkip($text)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->clickOnElementToSkip($text);
    }
}
