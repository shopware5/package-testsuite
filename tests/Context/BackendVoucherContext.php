<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Backend\VoucherModule;
use Shopware\Page\Frontend\CheckoutCart;

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
}