<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Index extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'myAccount' => '.account--link',
        ];
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
            'templateMainNav' => $xp
                ->nav(['@class' => 'navigation-main'])
                ->ul('dsc', ['~class' => 'navigation--list'])
                ->li('dsc', ['~text' => '{NAVTITLE}'])
                ->get(),
            'templateSubNav' => $xp
                ->li(['~class' => 'is--active'])
                ->a('dsc', ['@class' => 'navigation--link', 'and', '~text' => '{NAVTITLE}'])
                ->get(),
            'templateListingProductByOrderNumber' => $xp
                ->div(['@class' => 'listing--container'])
                ->div('dsc', ['@data-ordernumber' => '{ORDERNUMBER}'])
                ->get(),
            'templateListingProductBoxByName' => $xp
                ->div(['@class' => 'listing--container'])
                ->div('dsc', ['@class' => 'product--info'])
                ->a('dsc', ['@class' => 'product--title', 'and', '@title'=>'{PRODUCTNAME}'])
                ->div('asc', ['~class' => 'product--box'], 1)
                ->get(),
            'logoPictureSourceElements' => $xp
                ->div(['~class' => 'logo--shop'])
                ->picture('desc', [], 1)
                ->source('desc', [])
                ->get(),
            'logoPictureimgElement' => $xp
                ->div(['~class' => 'logo--shop'])
                ->picture('desc', [], 1)
                ->img('desc', [], 1)
                ->get(),
        ];
    }

    /**
     * @param string $subCategory
     * @return NodeElement|null
     */
    public function getSubNavElement($subCategory)
    {
        return $this->getNavElement($subCategory, 'templateSubNav');
    }

    /**
     * @param string $mainCategory
     * @return NodeElement|null
     */
    public function getMainNavElement($mainCategory)
    {
        return $this->getNavElement($mainCategory, 'templateMainNav');
    }

    /**
     * @param string $productname
     * @return NodeElement|null
     */
    public function getProductListingBoxElement($productname)
    {
        $xpath = $this->getXPathSelectors()['templateListingProductBoxByName'];
        return $this->find('xpath', str_replace('{PRODUCTNAME}', $productname, $xpath));
    }

    /**
     * @param string $title
     * @param string $template
     * @return NodeElement|null
     */
    public function getNavElement($title, $template)
    {
        $xpath = $this->getXPathSelectors()[$template];
        return $this->find('xpath', str_replace('{NAVTITLE}', $title, $xpath));
    }
}
