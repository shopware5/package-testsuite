<?php

namespace Shopware\Tests\Mink\Page\Installer;

use Shopware\Helper\ContextAwarePage;
use Shopware\Component\XpathBuilder\LegacyXpathBuilder;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

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
        $xp = new LegacyXpathBuilder();
        return [
            'backwardDbButton' => $xp->a(['~class' => 'btn-arrow-left'])->get(),
            'c_config_admin_email' => $xp->input(['@id' => 'c_config_admin_email'])->get(),
            'c_config_admin_name' => $xp->input(['@id' => 'c_config_admin_name'])->get(),
            'c_config_admin_password' => $xp->input(['@id' => 'c_config_admin_password'])->get(),
            'c_config_admin_username' => $xp->input(['@id' => 'c_config_admin_username'])->get(),
            'c_config_mail' => $xp->input(['@id' => 'c_config_mail'])->get(),
            'c_config_shopName' => $xp->input(['@id' => 'c_config_shopName'])->get(),
            'c_database_user' => $xp->input(['@name' => 'c_database_user'])->get(),
            'c_database_schema' => $xp->input(['@name' => 'c_database_schema'])->get(),
            'databaseForm' => $xp->form(['@action' => '/recovery/install/database-configuration/'])->get(),
            'forwardButton' => $xp->button(['@type' => 'submit', 'and', '~class' => 'btn-arrow-right'])->get(),
            'licenseCheckbox' => $xp->input(['@type' => 'checkbox', 'and', '@name' => 'eula'])->get(),
            'licenseForm' => $xp->form(['@action' => '/recovery/install/edition/'])->get(),
            'shopBackend' => $xp->a(['~class' => 'is--right'])->get(),
            'shopBasicConfiguration' => $xp->form(['@action' => '/recovery/install/configuration/'])->get(),
            'shopFrontend' => $xp->a(['~class' => 'is--left'])->get(),
            'start' => $xp->button(['@id' => 'start-ajax', 'and', '~class' => 'btn-database-right'])->get(),
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
     *
     */
    public function advance()
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->find('xpath', $xpath['forwardButton']);
        $forwardButton->click();
    }

    /**
     * Ticks the given checkbox
     *
     * @param string $identifier Xpath identifier from InstallerIndex
     */
    public function tickCheckbox($identifier)
    {
        $xpath = $this->getXPathSelectors();
        $checkboxButton = $this->find('xpath', $xpath[$identifier]);

        $checkboxButton->check();
    }

    /**
     * Checks, if a particular field is required
     *
     * @param string $field The given field of the form
     */
    public function checkRequiredFields($field)
    {
        $xpath = $this->getXPathSelectors();
        $this->find('xpath', $xpath[$field['fieldname']])->hasAttribute('required');
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
            'c_database_schema' => function($form, $fieldValue) {
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
        $xpath = $this->getXPathSelectors();
        $element = $this->find('xpath', $xpath[$text]);
        $element->click();
    }

    /**
     * Returns to the previous page when the database was already imported
     *
     */
    public function returnToPreviousDbPage()
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->find('xpath', $xpath['backwardDbButton']);
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
        $xpath = $this->getXPathSelectors();
        $shopLink = $this->find('xpath', $xpath[$type]);

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
