<?php

namespace Shopware\Page\Installer;

use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;
use Shopware\Component\Helper\Helper;
use Shopware\Component\Helper\HelperSelectorInterface;

class InstallerIndex extends ContextAwarePage implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/recovery/install/';

    /**
     * {@inheritdoc}
     */
    public function getXPathSelectors()
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
            'forwardButton' => $builder->reset()->child('button', ['@type' => 'submit', 'and', '~class' => 'btn-arrow-right'])->getXpath(),
            'licenseCheckbox' => $builder->reset()->child('input', ['@type' => 'checkbox', 'and', '@name' => 'eula'])->getXpath(),
            'shopFrontend' => $builder->reset()->child('a', ['~text' => 'Zum Shop-Frontend'])->getXpath(),
            'shopBackend' => $builder->reset()->child('a', ['~text' => 'Zum Shop-Backend'])->getXpath(),
            'start' => $builder->reset()->child('button', ['@id' => 'start-ajax', 'and', '~class' => 'btn-primary'])->getXpath(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
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
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Advances to the next page
     */
    public function advance()
    {
        $forwardButton = $this->find('xpath', $this->getXPathSelectors()['forwardButton']);
        $forwardButton->click();
    }

    /**
     * Ticks the given checkbox
     *
     * @param string $identifier Xpath identifier from InstallerIndex
     */
    public function tickCheckbox($identifier)
    {
        $checkboxButton = $this->find('xpath', $this->getXPathSelectors()[$identifier]);
        $checkboxButton->check();
    }

    /**
     * Checks, if a particular field is required
     *
     * @param string $field The given field of the form
     */
    public function checkRequiredFields($field)
    {
        $this->find('xpath', $this->getXPathSelectors()[$field['fieldname']])->hasAttribute('required');
    }

    /**
     * Fills in and submits the given form
     *
     * @param string $formname The name of the given form
     * @param array $data The data of the form
     */
    public function fillInAndSubmitForm($formname, $data)
    {
        Helper::fillForm($this, $formname, $data, [
            'c_database_schema' => function ($form, $fieldValue) {
                usleep(750000);
            }
        ]);
    }

    /**
     * Clicks an element with the given text
     *
     * @param string $text The text of the element
     */
    public function clickOnElementWithText($text)
    {
        $elementXpath = $this->getXPathSelectors()[$text];
        $this->waitForSelectorPresent('xpath', $elementXpath);
        $this->find('xpath', $this->getXPathSelectors()[$text])->click();
    }

    /**
     * Returns to the previous page when the database was already imported
     *
     */
    public function returnToPreviousDbPage()
    {
        $forwardButton = $this->find('xpath', $this->getXPathSelectors()['backwardDbButton']);
        $forwardButton->click();
    }

    /**
     * Chooses one of the options of a radio button
     *
     * @param string $value The label of the option
     */
    public function tickRadioButtonOption($value)
    {
        $css = $this->getCssSelectors();

        $ceEditionRadio = $this->find('css', $css[$value]);
        $ceEditionRadio->click();
    }

    /**
     * Checks if a field possesses the attribute "disbled"
     *
     * @param string $selector Defines which selector should be used
     * @param string $locator Indicates which field would be checked
     * @throws \Exception
     */
    public function checkIfDisabled($selector, $locator)
    {
        $css = $this->getCssSelectors();

        $element = $this->find($selector, $css[$locator]);
        $element->hasAttribute('disabled');

        if ($element->hasAttribute('disabled')) {
            throw new \Exception('License agreement field should be enabled in this case, but is not.');
        }
    }

    /**
     * Checks if the new shop is available after installation
     *
     * @param string $type Frontend or Backend of the shop
     * @param string $target Actual target of the link
     **/
    public function checkIfShopIsAvailable($type, $target)
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
    public function clickOnElementToSkip($text)
    {
        $this->find('named', ['link', $text])->click();
    }
}
