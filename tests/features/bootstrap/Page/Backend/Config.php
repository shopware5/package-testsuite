<?php
namespace Shopware\Tests\Mink\Page\Backend;

use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Config extends ContextAwarePage implements HelperSelectorInterface
{

    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Config';

    protected $subShopDefaults = [
        'name' => false,
        'host' => false,
        'url' => null,
        'currency' => 'Euro',
        'locale' => 'Deutsch (Deutschland)',
        'customer_group' => 'Shopkunden',
    ];

    /**
     * Returns an array of all xpath selectors of the element/page
     *
     * Example:
     * return [
     *  'loginform' = "//input[@id='email']/ancestor::form[1]",
     *  'loginemail' = "//input[@name='email']",
     *  'password' = "//input[@name='password']",
     * ]
     *
     * @return string[]
     */
    public function getXPathSelectors()
    {
        $xp = new XpathBuilder();
        return [
            'window' => $xp->xWindowByExactTitle('Grundeinstellungen')->get(),
            'windowShops' => $xp->xWindowByExactTitle('Grundeinstellungen - Shops')->get(),

            'ShopconfigTreeElement' => $xp->div('desc', ['@text' => 'Shopeinstellungen'])->td('asc', [], 1)->get(),
            'ShopsTreeElement' => $xp->div('desc', ['@text' => 'Shops'])->td('asc', [], 1)->get(),

            'AddButton' => $xp->span(['~class' => 'x-btn-inner', 'and', '~text' => 'Hinzufügen'])->button('asc', [], 1)->get(),

            'ShopName' => $xp->getXInputForLabel('Name:'),
            'ShopUrl' => $xp->getXInputForLabel('Virtuelle Url:'),
            'ShopPath' => $xp->getXInputForLabel('Pfad:'),

            'CategoryTreePebble' => $xp->getXSelectorPebbleForLabel('Kategorie:'),
            'CategoryTreeElementGerman' => $xp->div('desc', ['@text' => 'Deutsch'])->td('asc', [], 1)->get(),
        ];
    }

    /**
     * Returns an array of all css selectors of the element/page
     *
     * Example:
     * return [
     *  'image' = 'a > img',
     *  'link' = 'a',
     *  'text' = 'p'
     * ]
     *
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * Returns an array of all named selectors of the element/page
     *
     * Example:
     * return [
     *  'submit' = ['de' = 'Absenden',     'en' = 'Submit'],
     *  'reset'  = ['de' = 'Zurücksetzen', 'en' = 'Reset']
     * ]
     *
     * @return array[]
     */
    public function getNamedSelectors()
    {
        return [];
    }

    public function createSubShop(array $data)
    {
        $data = $this->checkSubShopData($data, $this->subShopDefaults);

        $xp = new XpathBuilder();
        $x = $this->getXPathSelectors();

        $this->waitForText('Weitere Einstellungen');

        $window = $this->find('xpath', $x['window']);
        $window->find('xpath', $x['ShopconfigTreeElement'])->click();
        $this->waitForText('Shops');
        $window->find('xpath', $x['ShopsTreeElement'])->click();
        $this->waitForText('Virtuelle Url');
        $addbutton = $this->find('xpath', $x['AddButton']);
        $addbutton->click();
        sleep(2);

        $window = $this->find('xpath', $x['windowShops']);

        $this->selectFromXDropdown($window, 'Shop-Typ:', 'typeSwitch', 'Subshop');
        $this->selectFromXDropdown($window, 'Währung:', 'currencyId', $data['currency']);
        $this->selectFromXDropdown($window, 'Lokalisierung:', 'localeId', $data['locale']);
        $this->selectFromXDropdown($window, 'Template:', 'templateId', 'Responsive');
        $this->selectFromXDropdown($window, 'Dokumenten-Template:', 'documentTemplateId', 'Responsive');
        $this->selectFromXDropdown($window, 'Kundengruppe:', 'customerGroupId', $data['customer_group']);

        $window->find('xpath', $x['ShopName'])->setValue($data['name']);
        if (array_key_exists('url', $data)) {
            $window->find('xpath', $x['ShopUrl'])->setValue($data['url']);
        }
        if (array_key_exists('path', $data)) {
            $window->find('xpath', $x['ShopPath'])->setValue($data['path']);
        }
        $window->find('xpath', $x['CategoryTreePebble'])->click();
        $categoryTreeElement = $this->waitForSelectorPresent('xpath', $x['CategoryTreeElementGerman']);
        $categoryTreeElement->click();
    }

    private function checkSubShopData(array $data, array $defaults)
    {
        $returnData = [];
        foreach ($defaults as $key => $value) {
            if (array_key_exists($key, $data) && !empty($data[$key])) {
                $returnData[$key] = $data[$key];
                continue;
            }
            if ($value === null) {
                continue;
            }
            if ($value === false) {
                throw new \Exception(sprintf("Value for %s must be set! Values given: %s", $key, print_r($data, true)));
            }
            $returnData[$key] = $value;
        }
        return $returnData;
    }
}
