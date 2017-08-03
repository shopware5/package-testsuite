<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Element\Frontend\Checkout\CartPosition;
use Shopware\Page\ContextAwarePage;
use Shopware\Component\Helper\HelperSelectorInterface;

class CheckoutCart extends ContextAwarePage implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'aggregationAmounts' => 'ul.aggregation--list',
            'sum' => 'li.entry--sum > div.entry--value',
            'shipping' => 'li.entry--shipping > div.entry--value',
            'total' => 'li.entry--total > div.entry--value',
            'sumWithoutVat' => 'li.entry--totalnet > div.entry--value',
            'taxValue' => 'li.entry--taxes:nth-of-type(%d) > div.entry--value',
            'taxRate' => 'li.entry--taxes:nth-of-type(%d) > div.entry--label',
            'addVoucherInput' => 'div.add-voucher--panel input.add-voucher--field',
            'addVoucherSubmit' => 'div.add-voucher--panel button.add-voucher--button',
            'addArticleInput' => 'form.add-product--form > input.add-product--field',
            'addArticleSubmit' => 'form.add-product--form > button.add-product--button',
            'removeVoucher' => 'div.row--voucher .column--actions-link',
            'aggregationLabels' => 'ul.aggregation--list .entry--label',
            'aggregationValues' => 'ul.aggregation--list .entry--value',
            'shippingPaymentForm' => 'form.payment',
            'articleDeleteButtons' => '.column--actions-link[title="Löschen"]',
            'dispatchSelect' => '#basket_dispatch_list',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [
            'addProductInput' => FrontendXpathBuilder::create()
                ->child('input', ['~class' => 'add-product--field'])
                ->getXpath(),
            'addProductSubmit' => FrontendXpathBuilder::create()
                ->child('button', ['~class' => 'add-product--button'])
                ->getXpath(),
            'cartPositionRow' => FrontendXpathBuilder::create()
                ->child('div', ['~class' => 'row--product'])
                ->getXpath(),
            'cartPositionName' => FrontendXpathBuilder::create()
                ->child('a', ['~class' => 'content--title'])
                ->getXpath(),
            'cartPositionNumber' => FrontendXpathBuilder::create()
                ->child('p', ['~class' => 'content--sku'])
                ->getXpath(),
            'cartPositionItemPrice' => FrontendXpathBuilder::create()
                ->child('div', ['~class' => 'column--unit-price'])
                ->getXpath(),
            'cartPositionTotalPrice' => FrontendXpathBuilder::create()
                ->child('div', ['~class' => 'column--total-price'])
                ->getXpath(),
        ];
    }

    /**
     * Validate the cart contains the given expected cart positions
     * @param array $positionData
     */
    public function validateCart(array $positionData)
    {
        $expectedPositions = [];
        foreach ($positionData as $position) {
            $expectedPositions[] = CartPosition::fromArray($position);
        }

        $actualPositions = $this->extractActualCartPositions();
        $this->assertCartPositionListsAreEqual($expectedPositions, $actualPositions);
    }

    /**
     * Adds an article to the cart
     * @param string $article
     */
    public function addArticle($article)
    {
        $addProductInputXpath = $this->getXPathSelectors()['addProductInput'];
        $addProductSubmitXpath = $this->getXPathSelectors()['addProductSubmit'];

        $this->waitForSelectorPresent('xpath', $addProductInputXpath);
        $this->waitForSelectorPresent('xpath', $addProductSubmitXpath);

        $this->find('xpath', $addProductInputXpath)->setValue($article);
        $this->find('xpath', $addProductSubmitXpath)->click();
    }

    /**
     * Remove the cart position at the specified index
     *
     * @param int $position
     * @throws \Exception
     */
    public function removeCartPositionAtIndex($position)
    {
        $this->open();
        $rows = $this->findAll('xpath', $this->getXPathSelectors()['cartPositionRow']);
        $position = (int)$position;

        if ($position > count($rows)) {
            throw new \Exception(sprintf('Can\'t delete cart position #%s - no such position', $position));
        }

        /** @var NodeElement $row */
        $row = $rows[$position - 1];
        $row->findButton('Löschen')->click();
    }

    /**
     * Checks the aggregation
     * @param $aggregation
     * @throws \Exception
     */
    public function checkAggregation($aggregation)
    {
        $this->open();

        foreach ($aggregation as $row) {
            switch ($row['label']) {
                case 'sum':
                    $element = $this->find('css', $this->getCssSelectors()['sum']);
                    $value = $element->getText();
                    if (self::toFloat($value) !== self::toFloat($row['value'])) {
                        throw new \Exception('Expected cart sum to be ' . $row['value'] . ', got ' . $value);
                    }
                    break;
                case 'shipping':
                    $element = $this->find('css', $this->getCssSelectors()['shipping']);
                    $value = $element->getText();
                    if (self::toFloat($value) !== self::toFloat($row['value'])) {
                        throw new \Exception('Expected shipping to be ' . $row['value'] . ', got ' . $value);
                    }
                    break;
                case 'total':
                    $element = $this->find('css', $this->getCssSelectors()['total']);
                    $value = $element->getText();
                    if (self::toFloat($value) !== self::toFloat($row['value'])) {
                        throw new \Exception('Expected total to be ' . $row['value'] . ', got ' . $value);
                    }
                    break;
                case 'sumWithoutVat':
                    $element = $this->find('css', $this->getCssSelectors()['sumWithoutVat']);
                    $value = $element->getText();
                    if (self::toFloat($value) !== self::toFloat($row['value'])) {
                        throw new \Exception('Expected sum without vat to be ' . $row['value'] . ', got ' . $value);
                    }
                    break;
            }
        }
    }

    /**
     * Fills the cart with products
     * @param array $items
     */
    public function fillCartWithProducts(array $items)
    {
        $originalPath = $this->path;

        foreach ($items as $item) {
            if (!$this->hasCartProductWithQuantity($item['number'], $item['quantity'])) {
                $this->path = sprintf('/checkout/addArticle/sAdd/%s/sQuantity/%d', $item['number'], $item['quantity']);
                $this->open();
            }
        }

        $this->path = $originalPath;
    }

    /**
     * Remove all products from the cart
     */
    public function emptyCart()
    {
        $this->open();
        $rows = $this->findAll('xpath', $this->getXPathSelectors()['cartPositionRow']);

        /** @var NodeElement $row */
        foreach ($rows as $row) {
            $row->findButton('Löschen')->click();
        }
    }

    /**
     * Check the cart position count and cart sum
     *
     * @param string $quantity
     * @param string $amount
     * @throws \Exception
     */
    public function checkPositionCountAndCartSum($quantity, $amount)
    {
        if ($this->getCartPositionCount() !== (int)$quantity || $this->getCartSum() !== self::toFloat($amount)) {
            throw new \Exception(sprintf('Expected %s positions with a sum of %s, but got %s with a sum of %s',
                $quantity, $amount, $this->getCartPositionCount(), $this->getCartSum()));
        }
    }

    /**
     * Proceeds to the confirmation page
     */
    public function proceedToOrderConfirmation()
    {
        $this->open();
        $this->clickLink('Zur Kasse');
    }

    /**
     * Return number of positions in cart
     *
     * @return int
     */
    private function getCartPositionCount()
    {
        $this->open();
        $rows = $this->findAll('xpath', $this->getXPathSelectors()['cartPositionRow']);
        return count($rows);
    }

    /**
     * Return current cart sum
     *
     * @return float
     */
    private function getCartSum()
    {
        $this->open();
        $element = $this->find('css', $this->getCssSelectors()['sum']);
        return self::toFloat($element->getText());
    }

    /**
     * Extract the positions currently in the user's cart
     *
     * @return CartPosition[]
     */
    private function extractActualCartPositions()
    {
        $this->open();
        $rows = $this->findAll('xpath', $this->getXPathSelectors()['cartPositionRow']);

        $positions = [];

        /** @var NodeElement $row */
        foreach ($rows as $row) {
            $positions[] = CartPosition::fromArray([
                'name' => $row->find('xpath', $this->getXPathSelectors()['cartPositionName'])->getText(),
                'number' => $row->find('xpath', $this->getXPathSelectors()['cartPositionNumber'])->getText(),
                'quantity' => $row->find('css', 'div.column--quantity option[selected]')->getText(),
                'itemPrice' => $row->find('xpath', $this->getXPathSelectors()['cartPositionItemPrice'])->getText(),
                'sum' => $row->find('xpath', $this->getXPathSelectors()['cartPositionTotalPrice'])->getText(),
            ]);
        }

        return $positions;
    }

    /**
     * Compare two lists of cart positions for equality
     *
     * @param CartPosition[] $expected
     * @param CartPosition[] $actual
     * @throws \Exception
     */
    private function assertCartPositionListsAreEqual(array $expected, array $actual)
    {
        if (count($expected) !== count($actual)) {
            throw new \Exception(sprintf('Expected %s cart positions, got %s.', count($expected), count($actual)));
        }

        /** @var CartPosition $expectedPosition */
        foreach ($expected as $expectedPosition) {
            /** @var CartPosition $actualPosition */
            foreach ($actual as $actualPosition) {
                if ($expectedPosition->getName() === $actualPosition->getName()) {
                    if ($this->compareCartPositions($expectedPosition, $actualPosition)) {
                        continue 2;
                    }

                    throw new \Exception(sprintf('Cart positions not as expected: Expected: %s Got: %s',
                            print_r($expectedPosition, true),
                            print_r($actualPosition, true))
                    );
                }
            }

            throw new \Exception(sprintf('Could not find position %s', print_r($expectedPosition, true)));
        }
    }

    /**
     * Compare two cart positions for equality
     *
     * @param CartPosition $expected
     * @param CartPosition $actual
     * @return bool
     */
    private function compareCartPositions(CartPosition $expected, CartPosition $actual)
    {
        return $actual->getName() === $expected->getName() &&
            strpos($actual->getNumber(), $expected->getNumber()) &&
            $actual->getQuantity() === $expected->getQuantity() &&
            $actual->getItemPrice() === $expected->getItemPrice() &&
            $actual->getSum() === $expected->getSum();
    }

    /**
     * Returns true if the cart contains a product with a given number and a given quantity
     *
     * @param $number
     * @param $quantity
     * @return bool
     */
    private function hasCartProductWithQuantity($number, $quantity)
    {
        $xPath = "//p[contains(text(), '" . $number . "')]//ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' row--product ')]//input[@name='sArticle' and @value='" . $quantity . "']";
        return $this->has('xpath', $xPath);
    }

    /**
     * Convert a given string value to a float
     *
     * @param $string
     * @return float
     */
    private static function toFloat($string)
    {
        if (is_float($string)) {
            return $string;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $string);
        preg_match("/([0-9]+[\\.]?[0-9]*)/", $float, $matches);

        return floatval($matches[0]);
    }

    /**
     * Add voucher to the cart
     *
     * @param string $code
     */
    public function addVoucher($code)
    {
        $this->open();

        $voucherCheckboxXpath = FrontendXpathBuilder::getInputById('add-voucher--trigger');
        $this->waitForSelectorPresent('xpath', $voucherCheckboxXpath);
        $this->find('xpath', $voucherCheckboxXpath)->click();

        $voucherInputXpath = FrontendXpathBuilder::getElementXpathByName('input', 'sVoucher');
        $this->waitForSelectorPresent('xpath', $voucherInputXpath);
        $this->find('xpath', $voucherInputXpath)->setValue($code);

        $voucherSubmitXpath = FrontendXpathBuilder::create($voucherInputXpath)->followingSibling('button')->getXpath();
        $this->waitForSelectorPresent('xpath', $voucherSubmitXpath);
        $this->find('xpath', $voucherSubmitXpath)->click();
    }

    /**
     * @inheritdoc
     */
    protected function verifyPage()
    {
        if (!strpos($this->getHtml(), 'is--ctl-checkout is--act-cart')) {
            throw new \Exception('Could not verify page - expected to be on checkout/cart.');
        }

        return true;
    }
}
