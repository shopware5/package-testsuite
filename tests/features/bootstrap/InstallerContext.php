<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\Page\Installer\InstallerIndex;

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
     */
    public function iShouldSeeText(TableNode $table)
    {
        $data = $table->getHash();
        foreach ($data as $text) {
            $this->checkIfThereIsText($text['text'], $this);
        }
    }

    /**
     * @When I check the :labeltext checkbox to agree to the terms
     */
    public function iCheckTheCheckbox($labeltext)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->tickCheckbox('licenseCheckbox');
    }

    /**
     * @Then the following :formname form fields must be required:
     */
    public function theFollowingFormFieldsMustBeRequired($formname, TableNode $table)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $data = $table->getHash();

        foreach ($data as $field) {
            $page->checkRequiredFields($field);
        }
    }

    /**
     * @When I fill the :formname form:
     */
    public function iFillTheForm($formname, TableNode $table)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $data = $table->getHash();

        $page->fillInAndSubmitForm($formname, $data);
    }

    /**
     * @When I click on :text on the installer page to start the database update
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
     */
    public function iChooseTheRadioFieldWithValue($value)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->tickRadioButtonOption($value);
    }

    /**
     * @Given I should see the link :linktext leading to :target
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
        $xp = new XpathBuilder();
        $this->waitForTextInElement($xp->div(['@class' => 'counter-text'])->get(), $text, 0, 120);
    }

    /**
     * @Then the :field field should get activated so that I am able to enter the license
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
     */
    public function iSkip($text)
    {
        /** @var InstallerIndex $page */
        $page = $this->getPage('InstallerIndex');
        $page->clickOnElementToSkip($text);
    }
}
