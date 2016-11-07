<?php
namespace Shopware\Tests\Mink\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\HelperSelectorInterface;
use Shopware\Helper\XpathBuilder;

class RiskManagement extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=RiskManagement';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        $xp = new XpathBuilder();
        return [
            'window' => $xp
                ->xWindowByTitle('Risk-Management')
                ->get(),
            'paymentSelectorPebble' => $xp
                ->div('desc', ['@text' => 'Zahlungsart wählen'])
                ->fieldset('asc', [], 1)
                ->div('desc', ['~class' => 'x-form-arrow-trigger'])
                ->get(),
            'paymentEntry' => $xp
                ->div('desc', ['~text' => '{TYPE}', 'and', '@style' => 'background-color:#f08080;'])
                ->get(),
            'deleteablePaymentEntries' => $xp
                ->div('desc', ['@text' => 'Sperre Zahlungsart WENN'])
                ->fieldset('asc', [], 1)
                ->span('desc', ['@text' => 'Löschen'])
                ->button('asc', [], 1)
                ->get(),
        ];
    }
}
