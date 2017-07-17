<?php

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

class AccountOrder extends MultipleElement
{
    /**
     * @inheritdoc
     */
    public function getDateProperty()
    {
        $elements = Helper::findElements($this, ['date', 'footerDate']);

        $dates = [
            'orderDate' => $elements['date']->getText(),
            'footerDate' => $elements['footerDate']->getText()
        ];

        return Helper::getUnique($dates);
    }

    /**
     * Returns the order positions
     * @param string[] $locators
     * @return array[]
     */
    public function getPositions($locators = ['product', 'currentPrice', 'quantity', 'price', 'sum'])
    {
        $selectors = Helper::getRequiredSelectors($this, $locators);
        $elements = Helper::findAllOfElements($this, ['positions']);
        $positions = [];

        /** @var NodeElement $position */
        foreach ($elements['positions'] as $position) {
            $positions[] = $this->getOrderPositionData($position, $selectors);
        }

        return $positions;
    }

    /**
     * Helper function returns the data of an order position
     * @param NodeElement $position
     * @param string[] $selectors
     * @return array
     */
    private function getOrderPositionData(NodeElement $position, array $selectors)
    {
        $data = [];

        foreach ($selectors as $key => $selector) {
            $element = $position->find('css', $selector);

            $data[$key] = $element->getText();

            if ($key !== 'product') {
                $data[$key] = Helper::floatValue($data[$key]);
            }
        }

        return $data;
    }
}
