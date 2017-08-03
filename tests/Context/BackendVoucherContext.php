<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Page\Backend\Backend;
use Shopware\Page\Backend\VoucherModule;
use Shopware\Page\Frontend\Account;
use Shopware\Page\Frontend\CheckoutCart;
use Shopware\Page\Frontend\CheckoutConfirm;

class BackendVoucherContext extends SubContext
{
    /**
     * @When I fill out the voucher form:
     * @param TableNode $table
     */
    public function fillNewVoucherForm(TableNode $table)
    {
        /** @var VoucherModule $page */
        $page = $this->getPage('VoucherModule');
        $data = $table->getHash();

        $page->fillVoucherEditorFormWith($data);
    }

    /**
     * @When I click the edit icon on the voucher named :name
     * @param string $name
     */
    public function iClickTheEditIconOnVoucher($name)
    {
        /** @var VoucherModule $page */
        $page = $this->getPage('VoucherModule');
        $page->openEditFormForVoucher($name);
    }

    /**
     * @Given I click the delete icon on the voucher named :name
     * @param $name
     */
    public function iClickTheDeleteIconOnTheVoucherNamed($name)
    {
        /** @var VoucherModule $page */
        $page = $this->getPage('VoucherModule');
        $page->deleteVoucher($name);
    }

    /**
     * @Given I add the voucher :code to my cart
     * @param string $code
     */
    public function iAddTheVoucherToMyCart($code)
    {
        /** @var CheckoutCart $page */
        $page = $this->getPage('CheckoutCart');
        $page->addVoucher($code);
    }

    /**
     * @Given I should be able to use the code exactly once
     */
    public function iShouldBeAbleToUseTheCodeExactlyOnce()
    {
        $voucherCode = $this->getVoucherCodeFromPage();

        $this->loginAsFrontendUser();
        $this->fillCartWithProductsAndGeneratedVoucher($voucherCode);
        $this->finishCheckout();
        $usedCode = $this->getUsedVoucherCodeFromBackend();

        \PHPUnit_Framework_Assert::assertEquals($voucherCode, $usedCode);
    }

    /**
     * Get the first dynamically generated voucher code
     *
     * @param bool $voucherWasUsed
     * @return string
     */
    private function getVoucherCodeFromPage($voucherWasUsed = false)
    {
        $voucherWasUsed = $voucherWasUsed ? 'Ja' : 'Nein';

        /** @var VoucherModule $page */
        $page = $this->getPage('VoucherModule');
        $codeXpathSelector = BackendXpathBuilder::create()
            ->child('div')
            ->contains($voucherWasUsed)
            ->ancestor('td', [], 1)
            ->precedingSibling('td')
            ->child('div', ['~class' => 'x-grid-cell-inner'])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $codeXpathSelector);

        return $page->find('xpath', $codeXpathSelector)->getText();
    }

    private function loginAsFrontendUser()
    {
        /** @var Account $accountPage */
        $accountPage = $this->getPage('Account');
        $accountPage->login('regular.customer@shopware.de.test', 'shopware');
    }

    /**
     * @param string $voucherCode
     */
    private function fillCartWithProductsAndGeneratedVoucher($voucherCode)
    {
        /** @var CheckoutCart $cartPage */
        $cartPage = $this->getPage('CheckoutCart');
        $cartPage->fillCartWithProducts([
            ['number' => 'SWT0001', 'quantity' => 1],
        ]);
        $cartPage->addVoucher($voucherCode);
    }

    private function finishCheckout()
    {
        /** @var CheckoutConfirm $confirmPage */
        $confirmPage = $this->getPage('CheckoutConfirm');
        $confirmPage->proceedToCheckout();
        $this->waitForText('Vielen Dank', 6);
    }

    /**
     * @return string
     */
    private function getUsedVoucherCodeFromBackend()
    {
        /** @var VoucherModule $voucherModule */
        $voucherModule = $this->getPage('VoucherModule');
        $voucherModule->open();

        $voucherModule->openEditFormForVoucher('Neuer Individueller Testgutschein');
        $this->waitForText('Gutschein-Konfiguration', 6);

        /** @var Backend $page */
        $backend = $this->getPage('Backend');
        $backend->clickOnTabWithName('Individuelle Gutscheincodes');

        $usedCode = $this->getVoucherCodeFromPage(true);
        return $usedCode;
    }
}