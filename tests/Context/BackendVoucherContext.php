<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Exception;
use PHPUnit\Framework\Assert;
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
     */
    public function fillNewVoucherForm(TableNode $table): void
    {
        $this->getModulePage()->fillVoucherEditorFormWith($table->getHash());
    }

    /**
     * @When I click the edit icon on the voucher named :name
     */
    public function iClickTheEditIconOnVoucher(string $name): void
    {
        $this->getModulePage()->openEditFormForVoucher($name);
    }

    /**
     * @Given I click the delete icon on the voucher named :name
     */
    public function iClickTheDeleteIconOnTheVoucherNamed(string $name): void
    {
        $this->getModulePage()->deleteVoucher($name);
    }

    /**
     * @Given I add the voucher :code to my cart
     */
    public function iAddTheVoucherToMyCart(string $code): void
    {
        $page = $this->getValidPage(CheckoutCart::class);
        $page->addVoucher($code);
    }

    /**
     * @Given I should be able to use the code exactly once
     *
     * @throws Exception
     */
    public function iShouldBeAbleToUseTheCodeExactlyOnce(): void
    {
        $voucherCode = $this->getVoucherCodeFromPage();

        $this->loginAsFrontendUser();
        $this->fillCartWithProductsAndGeneratedVoucher($voucherCode);
        $this->finishCheckout();
        $usedCode = $this->getUsedVoucherCodeFromBackend();

        Assert::assertEquals($voucherCode, $usedCode);
    }

    private function getModulePage(): VoucherModule
    {
        return $this->getValidPage(VoucherModule::class);
    }

    /**
     * Get the first dynamically generated voucher code
     */
    private function getVoucherCodeFromPage(bool $voucherWasUsed = false): string
    {
        $voucherWasUsedText = $voucherWasUsed ? 'Ja' : 'Nein';

        $codeXpathSelector = BackendXpathBuilder::create()
            ->child('div')
            ->contains($voucherWasUsedText)
            ->ancestor('td', [], 1)
            ->precedingSibling('td')
            ->child('div', ['~class' => 'x-grid-cell-inner'])
            ->getXpath();

        return $this->waitForSelectorPresent('xpath', $codeXpathSelector)->getText();
    }

    private function loginAsFrontendUser(): void
    {
        $accountPage = $this->getValidPage(Account::class);
        $accountPage->login('regular.customer@shopware.de.test', 'shopware');
    }

    /**
     * @throws Exception
     */
    private function fillCartWithProductsAndGeneratedVoucher(string $voucherCode): void
    {
        $cartPage = $this->getValidPage(CheckoutCart::class);
        $cartPage->fillCartWithProducts([
            ['number' => 'SWT0001', 'quantity' => '1'],
        ]);
        $cartPage->addVoucher($voucherCode);
    }

    private function finishCheckout(): void
    {
        $confirmPage = $this->getValidPage(CheckoutConfirm::class);
        $confirmPage->proceedToCheckout();
        $this->waitForText('Vielen Dank', 6);
    }

    private function getUsedVoucherCodeFromBackend(): string
    {
        $voucherModule = $this->getValidPage(VoucherModule::class);
        $voucherModule->open();

        $this->waitForText('Neuer Individueller Testgutschein', 3);

        $voucherModule->openEditFormForVoucher('Neuer Individueller Testgutschein');
        $this->waitForText('Gutschein-Konfiguration', 6);

        $backend = $this->getValidPage(Backend::class);
        $backend->clickOnTabWithName('Individuelle Gutscheincodes');

        return $this->getVoucherCodeFromPage(true);
    }
}
