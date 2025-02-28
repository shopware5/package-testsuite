<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

class VoucherModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Voucher';

    protected string $moduleWindowTitle = 'Gutschein-Administration';

    protected string $editorWindowTitle = 'Gutschein-Konfiguration';

    /**
     * Fill the new voucher form of the backend module with
     * the supplied data.
     */
    public function fillVoucherEditorFormWith(array $data): void
    {
        $window = $this->getEditorWindow();
        $this->fillExtJsForm($window, $data);
    }

    /**
     * Click the edit icon for the row containing $name
     */
    public function openEditFormForVoucher(string $name): void
    {
        $voucherRow = $this->getModuleWindow()->getGridView()->getRowByContent($name);
        $voucherRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Delete a voucher by its name
     */
    public function deleteVoucher(string $name): void
    {
        $voucherRow = $this->getModuleWindow()->getGridView()->getRowByContent($name);
        $voucherRow->clickActionIcon('sprite-minus-circle-frame');
    }
}
