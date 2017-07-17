<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Component\Helper\HelperSelectorInterface;

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
        $builder = new FrontendXpathBuilder();

        return [
            'templateMainNav' => $builder
                ->reset()
                ->child('nav', ['@class' => 'navigation-main'])
                ->descendant('ul', ['~class' => 'navigation--list'])
                ->descendant('li', ['~text' => '{NAVTITLE}'])
                ->getXpath(),

            'templateSubNav' => $builder
                ->reset()
                ->child('li', ['~class' => 'is--active'])
                ->descendant('a', ['@class' => 'navigation--link', 'and', '~text' => '{NAVTITLE}'])
                ->getXpath(),

            'templateListingProductByOrderNumber' => $builder
                ->reset()
                ->child('div', ['@class' => 'listing--container'])
                ->descendant('div', ['@data-ordernumber' => '{ORDERNUMBER}']),

            'templateListingProductBoxByName' => $builder
                ->reset()
                ->child('div', ['@class' => 'listing--container'])
                ->descendant('div', ['@class' => 'product--info'])
                ->descendant('a', ['@class' => 'product--title', 'and', '@title' => '{PRODUCTNAME}'])
                ->ancestor('div', ['~class' => 'product--box'], 1)
                ->getXpath(),

            'logoPictureSourceElements' => $builder
                ->reset()
                ->child('div', ['~class' => 'logo--shop'])
                ->descendant('picture', [], 1)
                ->descendant('source')
                ->getXpath(),

            'logoPictureimgElement' => $builder
                ->reset()
                ->child('div', ['~class' => 'logo--shop'])
                ->descendant('picture', [], 1)
                ->descendant('img', [], 1)
                ->getXpath(),
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
