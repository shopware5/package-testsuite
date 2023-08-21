<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Installer\InstallerIndex;

class InstallerContext extends SubContext
{
    /**
     * @When I advance to the next installer page
     */
    public function iAdvanceToTheNextInstallerPage(): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->advance();
    }

    /**
     * @Then I should see the list of required files and folders:
     * @Then I should see the checks for my system:
     */
    public function iShouldSeeText(TableNode $table): void
    {
        foreach ($table->getHash() as $text) {
            $this->checkIfThereIsText($text['text'], $this);
        }
    }

    /**
     * @When I check the license checkbox to agree to the terms
     */
    public function iCheckTheCheckbox(): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->tickCheckbox('tos');
    }

    /**
     * @Then the following form fields must be required:
     */
    public function theFollowingFormFieldsMustBeRequired(TableNode $table): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $data = $table->getHash();

        foreach ($data as $field) {
            $page->checkRequiredFields($field);
        }
    }

    /**
     * @When I fill the form:
     */
    public function iFillTheForm(TableNode $table): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $data = $table->getHash();

        $page->fillInAndSubmitForm($data);
    }

    /**
     * @When I click on :text on the installer page to start the database update
     */
    public function iClickOn(string $text): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->clickOnElementWithText($text);
    }

    /**
     * @When I go back to the previous installer page
     */
    public function iGoBackToThePreviousInstallerPage(): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->returnToPreviousDbPage();
    }

    /**
     * @When I choose the radio field with value :value
     */
    public function iChooseTheRadioFieldWithValue(string $value): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->tickRadioButtonOption($value);
    }

    /**
     * @Given I should see the link :linktext leading to :target
     */
    public function iShouldSeeTheLinkLeadingTo(string $linktext, string $target): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->checkIfShopIsAvailable($linktext, $target);
    }

    /**
     * @Then I should see :text after import is finished
     */
    public function iShouldSeeAfterImportIsFinished($text): void
    {
        $builder = new BackendXpathBuilder();
        $this->waitForTextInElement($builder->child('div', ['@class' => 'counter-container'])->getXpath(), $text, 0, 120);
    }

    /**
     * Just for Shopware 5.2
     *
     * @Then I should see :text after the database import has finished
     */
    public function iShouldSeeTextAfterTheDatabaseImportHasFinished(string $text): void
    {
        $builder = new BackendXpathBuilder();
        $this->waitForTextInElement($builder->child('div', ['@class' => 'counter-text'])->getXpath(), $text, 0, 120);
    }

    /**
     * @Then the :field field should get activated so that I am able to enter the license
     */
    public function theFieldShouldGetActivated(string $field): void
    {
        usleep(250000);

        $page = $this->getValidPage(InstallerIndex::class);
        $page->checkIfDisabled('css', $field);
    }

    /**
     * @Given I click :text to skip the next installer page
     */
    public function iSkip(string $text): void
    {
        $page = $this->getValidPage(InstallerIndex::class);
        $page->clickOnElementToSkip($text);
    }
}
