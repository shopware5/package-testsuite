<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class VoucherModule extends BackendModule
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Voucher';

    /**
     * Fill the new voucher form of the backend module with
     * the supplied data.
     *
     * @param array $data
     */
    public function fillVoucherEditorFormWith(array $data)
    {
        $window = $this->getVoucherEditorWindow();

        $this->fillExtJsForm($window, $data);
    }

    /**
     * Click the edit icon for the row containing $name
     *
     * @param $name
     */
    public function openEditFormForVoucher($name)
    {
        $editIconXpath = BackendXpathBuilder::create()
            ->child('strong')
            ->contains($name)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => 'sprite-pencil'])
            ->getXpath();

        $this->waitForXpathElementPresent($editIconXpath);
        $editIcon = $this->find('xpath', $editIconXpath);
        $editIcon->click();
    }

    /**
     * Delete a voucher by its name
     *
     * @param $name
     */
    public function deleteVoucher($name)
    {
        $deleteIconXpath = BackendXpathBuilder::create()
            ->child('strong')
            ->contains($name)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => 'sprite-minus-circle-frame'])
            ->getXpath();

        $this->waitForXpathElementPresent($deleteIconXpath);
        $deleteIcon = $this->find('xpath', $deleteIconXpath);
        $deleteIcon->click();
    }

    /**
     * Helper method to get the "new voucher" window node element
     * @return NodeElement
     * @throws \Exception
     */
    private function getVoucherEditorWindow()
    {
        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle('Gutschein-Konfiguration');

        $window = $this->find('xpath', $windowXpath);
        if(!$window) {
            throw new \Exception('Could not find voucher module.');
        }

        return $window;
    }
}