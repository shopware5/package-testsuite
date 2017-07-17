<?php

namespace Shopware\Element\Frontend;

use Shopware\Element\MultipleElement;
use Shopware\Tests\Mink\Helper;

class CartPosition extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.row--product'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.table--content > a.content--title',
            'number' => 'div.table--content > p.content--sku',
            'thumbnailLink' => 'div.table--media a.table--media-link',
            'thumbnailImage' => 'div.table--media a.table--media-link > img',
            'quantity' => 'div.column--quantity option[selected]',
            'itemPrice' => 'div.column--unit-price',
            'sum' => 'div.column--total-price'
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
     * Returns the quantity
     * @return float
     */
    public function getQuantityProperty()
    {
        return $this->getFloatProperty('quantity');
    }

    /**
     * Returns the item price
     * @return float
     */
    public function getItemPriceProperty()
    {
        return $this->getFloatProperty('itemPrice');
    }

    /**
     * Returns the sum
     * @return float
     */
    public function getSumProperty()
    {
        return $this->getFloatProperty('sum');
    }

    /**
     * Helper method to read a float property
     * @param string $propertyName
     * @return float
     */
    protected function getFloatProperty($propertyName)
    {
        $element = Helper::findElements($this, [$propertyName]);
        return Helper::floatValue($element[$propertyName]->getText());
    }
}
