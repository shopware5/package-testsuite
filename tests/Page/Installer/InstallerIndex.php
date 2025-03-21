<?php

declare(strict_types=1);

namespace Shopware\Page\Installer;

use Exception;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class InstallerIndex extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $path = '/recovery/install/index.php';

    public function getXPathSelectors(): array
    {
        $builder = new FrontendXpathBuilder();

        return [
            'c_config_admin_email' => FrontendXpathBuilder::getInputById('c_config_admin_email'),
            'c_config_admin_name' => FrontendXpathBuilder::getInputById('c_config_admin_name'),
            'c_config_admin_password' => FrontendXpathBuilder::getInputById('c_config_admin_password'),
            'c_config_admin_username' => FrontendXpathBuilder::getInputById('c_config_admin_username'),
            'c_config_mail' => FrontendXpathBuilder::getInputById('c_config_mail'),
            'c_config_shopName' => FrontendXpathBuilder::getInputById('c_config_shopName'),
            'c_database_user' => FrontendXpathBuilder::getInputById('c_database_user'),
            'c_database_schema' => FrontendXpathBuilder::getInputById('c_database_schema'),
            'databaseForm' => FrontendXpathBuilder::getFormByAction('/recovery/install/database-configuration/'),
            'licenseForm' => FrontendXpathBuilder::getFormByAction('/recovery/install/edition/'),
            'shopBasicConfiguration' => FrontendXpathBuilder::getFormByAction('/recovery/install/configuration/'),
            'backwardDbButton' => $builder->reset()->child('a', ['~class' => 'btn-arrow-left'])->getXpath(),
            'forwardButton' => $builder->reset()->child('button',
                ['@type' => 'submit', 'and', '~class' => 'btn-arrow-right'])->getXpath(),
            'tos' => $builder->reset()->child('input',
                ['@type' => 'checkbox', 'and', '@name' => 'tos'])->getXpath(),
            'shopFrontend' => $builder->reset()->child('a', ['~text' => 'Zum Shop-Frontend'])->getXpath(),
            'shopBackend' => $builder->reset()->child('a', ['~text' => 'Zum Shop-Backend'])->getXpath(),
            'start' => $builder->reset()->child('button',
                ['@id' => 'start-ajax', 'and', '~class' => 'btn-primary'])->getXpath(),
        ];
    }

    public function getCssSelectors(): array
    {
        return [
            'ce' => '#optionsRadios1',
            'cm' => '#optionsRadios2',
            'licenseAgreement' => '#c_license',
            'databaseForm' => 'form[action="/recovery/install/database-configuration/"]',
            'shopBasicConfiguration' => 'form[action="/recovery/install/configuration/"]',
        ];
    }

    /**
     * Advances to the next page
     */
    public function advance(): void
    {
        $forwardButton = $this->waitForSelectorPresent('xpath', $this->getXPathSelectors()['forwardButton']);
        $forwardButton->click();
    }

    /**
     * Ticks the given checkbox
     *
     * @param string $identifier Xpath identifier from InstallerIndex
     */
    public function tickCheckbox(string $identifier): void
    {
        $checkboxButton = $this->find('xpath', $this->getXPathSelectors()[$identifier]);
        $checkboxButton->check();
    }

    /**
     * Checks, if a particular field is required
     *
     * @param array $field The given field of the form
     */
    public function checkRequiredFields(array $field): void
    {
        $this->find('xpath', $this->getXPathSelectors()[$field['fieldname']])->hasAttribute('required');
    }

    /**
     * Fills in and submits the given form
     *
     * @param array $data The data of the form
     */
    public function fillInAndSubmitForm(array $data): void
    {
        foreach ($data as $formElement) {
            $elementXpath = FrontendXpathBuilder::getElementXpathByName('input', $formElement['field']);
            $element = $this->waitForSelectorPresent('xpath', $elementXpath);

            if ($element->isVisible()) {
                $element->setValue($formElement['value']);
            }
        }
    }

    /**
     * Clicks an element with the given text
     *
     * @param string $text The text of the element
     */
    public function clickOnElementWithText(string $text): void
    {
        $elementXpath = $this->getXPathSelectors()[$text];
        $this->waitForSelectorPresent('xpath', $elementXpath);
        $this->find('xpath', $this->getXPathSelectors()[$text])->click();
    }

    /**
     * Returns to the previous page when the database was already imported
     */
    public function returnToPreviousDbPage(): void
    {
        $forwardButton = $this->find('xpath', $this->getXPathSelectors()['backwardDbButton']);
        $forwardButton->click();
    }

    /**
     * Chooses one of the options of a radio button
     *
     * @param string $value The label of the option
     */
    public function tickRadioButtonOption(string $value): void
    {
        $css = $this->getCssSelectors();

        $ceEditionRadio = $this->find('css', $css[$value]);
        $ceEditionRadio->click();
    }

    /**
     * Checks if a field possesses the attribute "disbled"
     *
     * @param string $selector Defines which selector should be used
     * @param string $locator  Indicates which field would be checked
     *
     * @throws Exception
     */
    public function checkIfDisabled(string $selector, string $locator): void
    {
        $css = $this->getCssSelectors();

        $element = $this->find($selector, $css[$locator]);
        $element->hasAttribute('disabled');

        if ($element->hasAttribute('disabled')) {
            throw new Exception('License agreement field should be enabled in this case, but is not.');
        }
    }

    /**
     * Checks if the new shop is available after installation
     *
     * @param string $type   Frontend or Backend of the shop
     * @param string $target Actual target of the link
     **/
    public function checkIfShopIsAvailable(string $type, string $target): void
    {
        $shopLink = $this->find('xpath', $this->getXPathSelectors()[$type]);

        $shopLink->hasLink($target);
        $shopLink->click();
    }

    /**
     * Handles the special case for skipping the database configuration
     *
     * @param string $text Text of the element used for skipping
     **/
    public function clickOnElementToSkip(string $text): void
    {
        $this->find('named', ['link', $text])->click();
    }
}
