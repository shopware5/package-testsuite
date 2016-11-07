<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Exception\NotFoundByXpathException;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\Page\Backend\BackendLogin;
use Shopware\Tests\Mink\Page\Backend\Payment;
use Shopware\Tests\Mink\Page\Backend\RiskManagement;
use Shopware\Tests\Mink\Page\Backend\Shipping;
use Symfony\Component\Config\Definition\Exception\Exception;

class BackendContext extends SubContext
{

    /**
     * @When I log in with user :user and password :password
     */
    public function iLogInWithUserAndPassword($user, $password)
    {
        $this->login($user, $password);
    }

    /**
     * @When I hover backend menu item :item
     */
    public function iHoverBackendMenuItem($item)
    {
        $this->getSession()->getDriver()->mouseOver("//span[text()='$item']");
    }

    /**
     * @When I click on backend menu item :item
     */
    public function iClickOnBackendMenuItem($item)
    {
        $this->getSession()->getDriver()->click("//span[text()='$item']/ancestor::a[1]");
    }

    /**
     * @When I click on backend menu item that contains :text
     */
    public function iClickOnBackendMenuItemThatContains($text)
    {
        $this->getSession()->getDriver()->click("//span[contains(., '$text')]/ancestor::a[1]");
    }

    /**
     * @When I edit the user with email :email
     */
    public function iEditTheUserWithEmail($email)
    {
        /** @var $row \Behat\Mink\Element\NodeElement */
        $row = $this->getSession()->getPage()->find('xpath', "//a[contains(.,'$email')]/ancestor::tr[1]");
        if (!$row) {
            throw new \Exception(sprintf('Cannot find any row on the page containing the text "%s"', $email));
        }

        $row->find('xpath', "/descendant::img[@data-qtip='Diesen Benutzer editieren']")->click();
    }

    /**
     * @When /^(?:I activate )?the API access for the user with e\-mail "([^"]*)"(?: is activated)?$/
     */
    public function iActivateTheAPIAccessForTheUserWithEMail($email)
    {
        $xp = new XpathBuilder();

        $this->login();

        $this->getPage('UserManager')->open();
        $this->waitForText('Backend Benutzer Administration');

        $this->iEditTheUserWithEmail($email);
        $this->waitForText('API-Zugang');

        $apiCheckboxLabelXPath = $xp
            ->div(['@text' => 'API-Zugang'])
            ->fieldset('asc',[],1)
            ->label('desc', ['@text' => 'Aktiviert']);

        $apiCheckboxXPath = $xp
            ->tr('asc',[],1)
            ->input('desc');

        /** @var NodeElement $apiCheckbox */
        $apiCheckboxLabel = $this->getSession()->getPage()->find('xpath', $apiCheckboxLabelXPath);
        $apiCheckbox = $apiCheckboxLabel->find('xpath', $apiCheckboxXPath);

        $apiKeyExisted = true;

        if (!$this->isChecked($apiCheckbox)) {
            $apiCheckbox->click();
            $apiKeyExisted = false;
        }

        $apiKey = $this->getSession()->getPage()->find('xpath',
            "//div[text()='API-Zugang']/ancestor::fieldset[1]//input[@name='apiKey']")->getValue();
        $userName = $this->getSession()->getPage()->find('xpath',
            "//div[text()='Login']/ancestor::fieldset[1]//input[@name='username']")->getValue();

        if (empty($apiKey)) {
            throw new Exception("API Key was not found");
        }

        if ($apiKeyExisted) {
            return;
        }

        $saveButton = $this->getSession()->getPage()->find('xpath',
            "//div[text()='API-Zugang']/ancestor::div[" . XpathBuilder::getContainsClassString('x-window') . "][1]/descendant::span[text()='Speichern']");
        $saveButton->click();

        $this->confirmPasswordDialog();

        $this->waitForText('Erfolgreich');
    }

    /**
     * @Given /^I am logged in with user "([^"]*)" and password "([^"]*)"$/
     */
    public function iAmLoggedInWithUserAndPassword($user, $password)
    {
        $this->iLogInWithUserAndPassword($user, $password);

        // waitForText() is defined in Trait
        $this->waitForText('Marketing');
    }

    /**
     * @When /^I create a new manufacturer with the following data:$/
     */
    public function iCreateANewManufacturerWithTheFollowingData(TableNode $table)
    {
        $addButton = $this->getSession()->getPage()->find('xpath', "//button[@data-action='addSupplier']");
        if ($addButton == null) {
            throw new ElementNotFoundException($this->getSession()->getDriver());
        }

        $addButton->click();

        $this->waitForText('Hersteller - Anlegen');

        $data = $table->getHash();
        $page = $this->getSession()->getPage();

        $window = $page->find('xpath',
            "//label[text()='Hersteller-Name:']/ancestor::div[" . XpathBuilder::getContainsClassString('x-window') . "][1]");
        $this->assertNotNull($window, "Manufacturer Window");

        $nameInput = $page->find('xpath',
            "//label[text()='Hersteller-Name:']/ancestor::td[1]/following-sibling::td[1]/input[@name='name']");
        $this->assertNotNull($nameInput, "Name Input");

        $pageTitleInput = $window->find('xpath',
            "//label[text()='Hersteller Seitentitel:']/ancestor::td[1]/following-sibling::td[1]/input[@name='metaTitle']");
        $this->assertNotNull($pageTitleInput, "Page Title Input");

        $urlInput = $window->find('xpath',
            "//label[text()='URL:']/ancestor::td[1]/following-sibling::td[1]/input[@name='link']");
        $this->assertNotNull($urlInput, "Url Input");

        foreach ($data as $tr) {
            switch ($tr['field']) {
                case 'name':
                    $nameInput->setValue($tr['value']);
                    break;
                case 'pageTitle':
                    $pageTitleInput->setValue($tr['value']);
                    break;
                case 'url':
                    $urlInput->setValue($tr['value']);
                    break;
                case 'description':
                    $this->getSession()->executeScript("tinymce.get()[0].setContent('" . $tr['value'] . "');");
                    break;
            }
        }

        $window->find('xpath', "//span[text()='Speichern']")->click();
    }

    /**
     * @param $var
     * @param string $elementname
     * @throws NotFoundByXpathException
     */
    private function assertNotNull($var, $elementname = "")
    {
        if ($var == null) {
            throw new NotFoundByXpathException($elementname);
        }
    }

    /**
     * @When /^I create a new article with the following data:$/
     */
    public function iCreateANewArticleWithTheFollowingData(TableNode $table)
    {
        $data = $table->getHash();

        // waitForText() is defined in Trait
        $this->waitForText('Prozentrabatt');

        $page = $this->getSession()->getPage();

        /** @var NodeElement $window */
        $window = $page->find('xpath',
            "//label[text()='Artikel-Bezeichnung:']/ancestor-or-self::div[" . XpathBuilder::getContainsClassString('x-window') . "][1]");
        $this->assertNotNull($window, "Create Article Window");

        $manufacturerSelector = $window->find('xpath',
            "//label[text()='Hersteller:']/ancestor::td[1]/following-sibling::td[1]//descendant::div[" . XpathBuilder::getContainsClassString('x-form-arrow-trigger') . "][1]");
        $this->assertNotNull($manufacturerSelector, "Manufacturer Selector Pebble");

        $nameInput = $window->find('xpath',
            "//label[text()='Artikel-Bezeichnung:']/ancestor::td[1]/following-sibling::td[1]//descendant::input[@name='name']");
        $this->assertNotNull($nameInput, "Name Input");

        $ekInput = $window->find('xpath',
            "//label[text()='Einkaufspreis:']/ancestor::td[1]/following-sibling::td[1]//descendant::input[@name='mainDetail[purchasePrice]']");
        $this->assertNotNull($ekInput, "EK Input");

        $priceFieldset = $window->find('xpath', "//span[text()='Shopkunden Brutto']/ancestor::fieldset[1]");
        $this->assertNotNull($priceFieldset, "Price Fieldset");

        /** @var NodeElement $priceCell */
        $priceCell = $priceFieldset->find('xpath',
            "//span[text()='Shopkunden Brutto']/ancestor::fieldset[1]/descendant::table[" . XpathBuilder::getContainsClassString('x-grid-table') . "][1]/descendant::td[3]");
        $this->assertNotNull($priceCell, "Price Cell");

        /** @var NodeElement $priceCell */
        $pseudoPriceCell = $priceFieldset->find('xpath',
            "//span[text()='Shopkunden Brutto']/ancestor::fieldset[1]/descendant::table[" . XpathBuilder::getContainsClassString('x-grid-table') . "][1]/descendant::td[5]");
        $this->assertNotNull($pseudoPriceCell, "Pseudo Price Cell");

        foreach ($data as $tr) {
            switch ($tr['field']) {
                case 'manufacturer':
                    $manufacturerSelector->click();
                    $manufacturerListEntry = $page->find('xpath',
                        "//div[@data-action='supplierId']//li[text()='$tr[value]']");
                    $this->assertNotNull($manufacturerListEntry, "Manufacturer List Entry");
                    $manufacturerListEntry->click();
                    break;
                case 'name':
                    $nameInput->setValue($tr['value']);
                    break;
                case 'ek':
                    $ekInput->setValue($tr['value']);
                    break;
                case 'price':
                    $priceCell->click();
                    // spin() is defined in Trait
                    $this->spin(function (BackendContext $context) {
                        return $context->getSession()->getPage()->find('xpath', "//input[@name='price']") !== null;
                    });
                    $priceInput = $page->find('xpath', "//input[@name='price']");
                    $this->assertNotNull($priceInput, "Price Input");
                    $priceInput->setValue($tr['value']);
                    break;
                case 'pseudoPrice':
                    $pseudoPriceCell->click();
                    $this->spin(function (BackendContext $context) {
                        return $context->getSession()->getPage()->find('xpath',
                            "//input[@name='pseudoPrice']") !== null;
                    });
                    $pseudoPriceInput = $page->find('xpath', "//input[@name='pseudoPrice']");
                    $this->assertNotNull($pseudoPriceInput, "Pseudo Price Input");
                    $pseudoPriceInput->setValue($tr['value']);
                    break;
                case 'description':
                    $this->getSession()->executeScript("tinymce.get()[0].setContent('" . $tr['value'] . "');");
                    break;
                case 'image':
                    $this->uploadArticleImage($tr['value'], $window);
                    break;
            }
        }
        $page->find('xpath', "//span[text()='Artikel speichern']")->click();
    }

    private function uploadArticleImage($localName, NodeElement $window)
    {
        $file = dirname(dirname(dirname(__FILE__))) . '/assets/images/article/' . $localName;
        $imageTab = $window->find('xpath',
            "//span[text()='Bilder' and " . XpathBuilder::getContainsClassString('x-tab-inner') . "]");
        $imageTab->click();
        $addImageButton = $window->find('xpath',
            "//span[text()='Bild hinzufügen' and " . XpathBuilder::getContainsClassString('x-btn-inner') . "]");
        $addImageButton->click();

        // waitForText() is defined in Trait
        $this->waitForText('Eigene Medien hinzufügen');

        $imageUploadField = $this->getSession()->getPage()->find('xpath',
            "//span[text()='Eigene Medien hinzufügen' and " . XpathBuilder::getContainsClassString('x-btn-inner') . "]/ancestor::div[1]/descendant::input[@type='file']");

        $tempZip = tempnam('', 'WebDriverZip');
        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE);
        $zip->addFile($file, basename($file));
        $zip->close();

        /** @var Selenium2Driver $driver */
        $driver = $this->getSession()->getDriver();

        $remotePath = $driver->getWebDriverSession()->file([
            'file' => base64_encode(file_get_contents($tempZip))
        ]);

        $this->getSession()->getPage()->attachFileToField($imageUploadField->getAttribute('id'), $remotePath);

        // spin() is defined in Trait
        $this->spin(function (BackendContext $context) {
            return $context->getSession()->getPage()->find("xpath",
                "//img[contains(@src,'feature_create_article_ChromeValley')]") !== null;
        });

        $thumb = $this->getSession()->getPage()->find("xpath",
            "//img[contains(@src,'feature_create_article_ChromeValley')]");
        $thumb->click();

        $this->getSession()->getPage()->find('xpath', "//span[text()='Auswahl übernehmen']")->click();
    }

    /**
     * Create a new backend account. The demo/demo account must exist already.
     *
     * @Given /^the backend account "([^"]*)"(?: with password "([^"]*)")? exists$/
     */
    public function theBackendAccountExists($email, $password = '')
    {
        $this->login();

        $this->getPage('UserManager')->open();
        $this->waitForText('Backend Benutzer Administration');
        $accountExists = $this->textExistsEventually($email, 5);
        if ($accountExists === false) {
            $this->createAccount($email, $password);
        }
    }

    private function createAccount($email, $password, $name = 'Max Mustermann', $language = 'Deutsch (Deutschland)')
    {
        $this->waitForText('Benutzer hinzufügen');
        $addUserButton = $this->getSession()->getPage()->find('xpath', "//span[contains(.,'Benutzer hinzufügen')][1]");
        $addUserButton->click();
        $this->waitForText('Benutzername:');

        $window = $this->getSession()->getPage()->find('xpath',
            "//label[text()='Benutzername:'][1]/ancestor::div[" . XpathBuilder::getContainsClassString('x-window') . "][1]");

        $usernameInput = $window->find('xpath', "/descendant::input[@name='username']");
        $username = $this->slugify($email);
        $usernameInput->setValue($username);

        // passwordFromEmail() is defined in trait
        $password = $password ?: $this->passwordFromEmail($email);

        $passwordInput = $window->find('xpath', "/descendant::input[@name='password']");
        $passwordInput->setValue($password);

        $passwordconfirmationInput = $window->find('xpath', "/descendant::input[@name='password2']");
        $passwordconfirmationInput->setValue($password);

        $activateAccountCheckbox = $window->find('xpath',
            "/descendant::div[contains(.,'Account aktivieren oder deaktivieren')]/ancestor::tbody[1]/descendant::input[@type='button' and " . XpathBuilder::getContainsClassString('x-form-checkbox') . "]");
        $activateAccountCheckbox->click();

        $nameInput = $window->find('xpath', "/descendant::input[@name='name']");
        $nameInput->setValue($name);

        $emailInput = $window->find('xpath', "/descendant::input[@name='email']");
        $emailInput->setValue($email);

        $languageSelector = $window->find('xpath',
            "//label[text()='Standardsprache:']/ancestor::td[1]/following-sibling::td[1]//descendant::div[" . XpathBuilder::getContainsClassString('x-form-arrow-trigger') . "][1]");
        $this->assertNotNull($languageSelector, "Language Selector Pebble");
        $languageSelector->click();
        $languageListEntry = $this->getSession()->getPage()->find('xpath',
            "//div[@data-action='locale']//li[text()='$language']");
        $this->assertNotNull($languageListEntry, "Language List Entry");
        $languageListEntry->click();

        $roleSelector = $window->find('xpath',
            "//label[text()='Mitglied der Rolle:']/ancestor::td[1]/following-sibling::td[1]//descendant::div[" . XpathBuilder::getContainsClassString('x-form-arrow-trigger') . "][1]");
        $this->assertNotNull($roleSelector, "Role Selector Pebble");
        $roleSelector->click();
        $roleListEntry = $this->getSession()->getPage()->find('xpath',
            "//div[@data-action='role']//li[text()='local_admins']");
        $this->assertNotNull($roleListEntry, "Role List Entry");
        $roleListEntry->click();

        $window->find('xpath', "/descendant::span[text()='Speichern']")->click();

        $this->confirmPasswordDialog();
        sleep(2);
    }

    /**
     * Login to backend
     *
     * @param $user
     * @param $password
     */
    private function login($user = 'demo', $password = 'demo')
    {
        /** @var BackendLogin $page */
        $page = $this->getPage('BackendLogin');
        $page->open();

        // See if we already are logged in
        if ($this->waitIfThereIsText('Marketing', 5)) {
            return;
        }

        // waitForText() is defined in Trait
        $this->waitForText('Shopware Backend Login', 10);

        $page->login($user, $password);
        $this->waitForText('Marketing');
    }

    /**
     * @param string $password
     */
    private function confirmPasswordDialog($password = 'demo')
    {
        $this->waitForText('Bitte geben Sie Ihr Passwort ein:');

        $messageBox = $this->getSession()->getPage()->find('xpath',
            "//span[text()='Passwort Überprüfung']/ancestor::div[" . XpathBuilder::getContainsClassString('x-message-box') . "][1]");

        $passwordField = $messageBox->find('xpath', "/descendant::input[@type='password']");
        $passwordField->setValue($password);

        $messageBox->find('xpath', "/descendant::span[text()='OK']")->click();
    }

    /**
     * @Given /^the payment (?:type|method) "([^"]*)" does not have risk management rules(?: and has a surcharge of "([^"]*)")?$/
     */
    public function theFollowingPaymentTypesHaveNoRiskManagementRules($type, $surcharge = 0)
    {
        $this->login();

        $this->activatePayment($type, ['surcharge' => $surcharge]);

        /** @var RiskManagement $page */
        $page = $this->getPage('RiskManagement');
        $page->open();

        $xPaths = $page->getXPathSelectors();

        $this->waitForText("Risk-Management");

        /** @var NodeElement $window */
        $window = $page->find('xpath', $xPaths['window']);
        $this->assertNotNull($window, "Risk Management window");

        /** @var NodeElement $paymentTypeSelector */
        $paymentTypeSelector = $window->find('xpath', $xPaths['paymentSelectorPebble']);
        $this->assertNotNull($paymentTypeSelector, "Payment Type Selector Pebble");
        $paymentTypeSelector->click();

        $this->waitForText($type);

        /** @var NodeElement $entry */
        $entry = $page->find('xpath', str_replace('{TYPE}', $type, $xPaths['paymentEntry']));
        if ($entry === null) {
            return;
        }
        $entry->click();

        $this->waitForText('Sperre Zahlungsart WENN');

        /** @var NodeElement[] $deleteables */
        $deleteables = $window->findAll('xpath', $xPaths['deleteablePaymentEntries']);
        foreach ($deleteables as $deleteable) {
            $deleteable->click();
            $this->waitForText('Regel erfolgreich gelöscht', 0);
            $this->waitForTextNotPresent('Regel erfolgreich gelöscht', 0);
        }
    }

    /**
     * @param string $type
     * @param array $options
     */
    private function activatePayment($type, $options = [])
    {
        /** @var Payment $page */
        $page = $this->getPage('Payment');
        $page->open();
        $this->waitForText('Zahlungsarten');

        $xp = new XpathBuilder();
        $windowXPath = $xp->xWindowByTitle('Zahlungsarten')->get();
        /** @var NodeElement $window */
        $window = $page->find('xpath', $windowXPath);
        $this->assertNotNull($window, "Payment window: $windowXPath");
        
        $typeElementXPath = $xp
            ->div('desc', ['starts-with' => ['@id', 'payment-main-tree']])
            ->div('desc', ['~class' => 'x-grid-cell-inner', 'and', '~text' => $type . ' ('])
            ->get();

        /** @var NodeElement $typeElement */
        $typeElement = $window->find('xpath', $typeElementXPath);
        $this->assertNotNull($typeElement, "Type element: $typeElementXPath");
        $typeElement->click();

        $tabXPath = $xp
            ->span('desc', ['@class' => 'x-tab-inner', 'and', '~text' => 'Generell'])
            ->get();

        /** @var NodeElement $tab */
        $tab = $window->find('xpath', $tabXPath);
        $this->assertNotNull($tab, "General Tab: $tabXPath");
        $tab->click();

        $checkboxXPath = $xp
            ->label('desc', ['~class' => 'x-form-item-label', 'and', '@text' => 'Aktiv:'])
            ->td('asc', [], 1)
            ->td('fs', [], 1)
            ->input('desc', [], 1)
            ->get();

        /** @var NodeElement $checkbox */
        $checkbox = $window->find('xpath', $checkboxXPath);
        $this->assertNotNull($checkbox, "Activation checkbox: $checkboxXPath");

        /* ExtJS checkboxes are buttons. Ancestor table tag indicates checked/unckecked via class. */
        $checkedXPath = $xp
            ->table('asc', ['~class' => 'x-form-cb-checked'], 1)
            ->get();
        /** @var NodeElement $checked */
        $checked = $checkbox->find('xpath', $checkedXPath);
        if ($checked !== null && count($options) === 0) {
            return;
        }

        if ($checked === null) {
            $checkbox->click();
        }

        foreach ($options as $key => $value) {
            $this->setPaymentOption($key, $value, $window);
        }

        $saveButtonXPath = $xp
            ->span('desc', ['@text' => 'Speichern', 'and', '~class' => 'x-btn-inner'])
            ->button('asc', [], 1)
            ->get();

        /** @var NodeElement $checkbox */
        $saveButton = $window->find('xpath', $saveButtonXPath);
        $this->assertNotNull($saveButton, "Save button: $saveButtonXPath");
        $saveButton->click();

        $this->waitForText('Zahlungsart gespeichert', 1);
    }

    private function setPaymentOption($key, $value, NodeElement $window)
    {
        $xp = new XpathBuilder();
        switch ($key) {
            case 'surcharge':
                $surchargeInputXPath = $xp
                    ->label('desc', ['@text' => 'Pauschaler Aufschlag:'])
                    ->tr('asc', [], 1)
                    ->input('desc', ['@name' => 'surcharge'])
                    ->get();
                /** @var NodeElement $surchargeInput */
                $surchargeInput = $window->find('xpath', $surchargeInputXPath);
                $this->assertNotNull($surchargeInput, "Type element: $surchargeInputXPath");
                $surchargeInput->setValue($value);
                break;
        }
    }

    /**
     * @Given the following shipping options exist:
     */
    public function theFollowingShippingOptionsExist(TableNode $table)
    {
        $data = $table->getHash();

        $this->login();

        /** @var Shipping $page */
        $page = $this->getPage('Shipping');

        foreach ($data as $shipping) {
            $page->createShippingMethodIfNotExists($shipping);
        }
    }

    /**
     * @Given the shipping method :method is active for the following payment methods:
     */
    public function theShippingMethodIsActiveForTheFollowingPamentMethods($method, TableNode $table)
    {
        /** @var Shipping $page */
        $page = $this->getPage('Shipping');
        $page->activatePaymentMethodsForShippingMethod($method, $table->getHash());
    }

    /**
     * @Given the shipping method :method is active for the following countries:
     */
    public function theShippingMethodIsActiveForTheFollowingCountries($method, TableNode $table)
    {
        /** @var Shipping $page */
        $page = $this->getPage('Shipping');
        $page->activateCountriesForShippingMethod($method, $table->getHash());
    }
}
