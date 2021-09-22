<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Index extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $path = '/';

    /**
     * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        return [
            'templateMainNav' => FrontendXpathBuilder::create()
                ->child('nav', ['@class' => 'navigation-main'])
                ->descendant('ul', ['~class' => 'navigation--list'])
                ->descendant('li', ['~text' => '{NAVTITLE}'])
                ->getXpath(),

            'templateSubNav' => FrontendXpathBuilder::create()
                ->child('li', ['~class' => 'is--active'])
                ->descendant('a', ['@class' => 'navigation--link', 'and', '~text' => '{NAVTITLE}'])
                ->getXpath(),

            'templateListingProductByOrderNumber' => FrontendXpathBuilder::create()
                ->child('div', ['@class' => 'listing--container'])
                ->descendant('div', ['@data-ordernumber' => '{ORDERNUMBER}']),

            'templateListingProductBoxByName' => FrontendXpathBuilder::create()
                ->child('div', ['@class' => 'listing--container'])
                ->descendant('div', ['@class' => 'product--info'])
                ->descendant('a', ['@class' => 'product--title', 'and', '@title' => '{PRODUCTNAME}'])
                ->ancestor('div', ['~class' => 'product--box'], 1)
                ->getXpath(),
        ];
    }

    /**
     * @param string $subCategory
     *
     * @return NodeElement|null
     */
    public function getSubNavElement($subCategory)
    {
        return $this->getNavElement($subCategory, 'templateSubNav');
    }

    /**
     * @param string $mainCategory
     *
     * @return NodeElement|null
     */
    public function getMainNavElement($mainCategory)
    {
        return $this->getNavElement($mainCategory, 'templateMainNav');
    }

    /**
     * @param string $productName
     *
     * @return NodeElement
     */
    public function getProductListingBoxElement($productName)
    {
        $xpath = $this->getXPathSelectors()['templateListingProductBoxByName'];

        return $this->find('xpath', str_replace('{PRODUCTNAME}', $productName, $xpath));
    }

    public function getNavElement(string $title, string $template): NodeElement
    {
        $xpath = $this->getXPathSelectors()[$template];

        return $this->find('xpath', str_replace('{NAVTITLE}', $title, $xpath));
    }
}
