<?php

declare(strict_types=1);

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

    public function getXPathSelectors(): array
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

    public function getSubNavElement(string $subCategory): ?NodeElement
    {
        return $this->getNavElement($subCategory, 'templateSubNav');
    }

    public function getMainNavElement(string $mainCategory): ?NodeElement
    {
        return $this->getNavElement($mainCategory, 'templateMainNav');
    }

    public function getProductListingBoxElement(string $productName): NodeElement
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
