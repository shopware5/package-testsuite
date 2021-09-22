<?php

namespace Shopware\Page\Backend;

class VoucherModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Voucher';

    /**
     * {@inheritdoc}
     */
    protected $moduleWindowTitle = 'Gutschein-Administration';

    /**
     * {@inheritdoc}
     */
    protected $editorWindowTitle = 'Gutschein-Konfiguration';

    /**
     * Fill the new voucher form of the backend module with
     * the supplied data.
     */
    public function fillVoucherEditorFormWith(array $data)
    {
        $window = $this->getEditorWindow();
        $this->fillExtJsForm($window, $data);
    }

    /**
     * Click the edit icon for the row containing $name
     */
    public function openEditFormForVoucher($name)
    {
        $voucherRow = $this->getModuleWindow()->getGridView()->getRowByContent($name);
        $voucherRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Delete a voucher by its name
     */
    public function deleteVoucher($name)
    {
        $voucherRow = $this->getModuleWindow()->getGridView()->getRowByContent($name);
        $voucherRow->clickActionIcon('sprite-minus-circle-frame');
    }
}
