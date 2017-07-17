<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Helper\ContextAwarePage;
use Shopware\Tests\Mink\Element\CartPosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
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
    public function getNamedSelectors()
    {
        return [
            'checkout' => ['de' => 'Zur Kasse',   'en' => 'Checkout'],
            'sum' => ['de' => 'Summe:', 'en' => 'Proceed to checkout'],
            'shipping' => ['de' => 'Versandkosten:', 'en' => 'Proceed to checkout'],
            'total' => ['de' => 'Gesamtsumme:', 'en' => 'Proceed to checkout'],
            'sumWithoutVat' => ['de' => 'Gesamtsumme ohne MwSt.:', 'en' => 'Proceed to checkout'],
            'tax' => ['de' => 'zzgl. %d %% MwSt.:', 'en' => 'Proceed to checkout'],
            'changePaymentButton' => ['de' => 'Weiter', 'en' => 'Next'],
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
        ];
    }

    /**
     * Checks the aggregation
     * @param $aggregation
     * @throws \Exception
     */
    public function checkAggregation($aggregation)
    {
        $elements = Helper::findAllOfElements($this, ['aggregationLabels', 'aggregationValues']);
        $lang = 'de';
        $check = [];

        foreach ($aggregation as $property) {
            $key = $this->getAggregationPosition($elements['aggregationLabels'], $property['label'], $lang);

            $check[$property['label']] = Helper::floatArray([
                $property['value'],
                $elements['aggregationValues'][$key]->getText(),
            ]);

            unset($elements['aggregationLabels'][$key]);
            unset($elements['aggregationValues'][$key]);
        }

        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The value of "%s" is "%s"! (should be "%s")',
                $result,
                $check[$result][1],
                $check[$result][0]
            );

            throw new \Exception($message);
        }
    }

    /**
     * @param string $key
     * @param string $language
     * @return string
     */
    private function getLabel($key, $language)
    {
        $language = $language ?: 'de';

        $labels = $this->getNamedSelectors();

        if (strpos($key, '%') !== false) {
            $taxRate = intval($key);
            return sprintf($labels['tax'][$language], $taxRate);
        }

        if (isset($labels[$key][$language])) {
            return $labels[$key][$language];
        }

        $message = sprintf('Label "%s" is not defined for language key "%s"', $key, $language);
        throw new \Exception($message);
    }

    /**
     * @param array $labels
     * @param string $labelKey
     * @param string $language
     * @return int
     * @throws \Exception
     */
    private function getAggregationPosition(array $labels, $labelKey, $language)
    {
        $language = $language ?: 'de';

        $givenLabel = $this->getLabel($labelKey, $language);

        $key = 0;
        $lastKey = max(array_keys($labels));

        do {
            if (array_key_exists($key, $labels)) {
                $readLabel = $labels[$key]->getText();

                if ($givenLabel === $readLabel) {
                    return $key;
                }
            }

            $key++;
        } while ($key <= $lastKey);

        $message = sprintf('Label "%s" does not exist on the page! ("%s")', $labelKey, $givenLabel);
        throw new \Exception($message);
    }

    /**
     * Adds a voucher to the cart
     * @param string $voucher
     */
    public function addVoucher($voucher)
    {
        $elements = Helper::findElements($this, ['addVoucherInput', 'addVoucherSubmit']);

        $elements['addVoucherInput']->setValue($voucher);
        $elements['addVoucherSubmit']->press();
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
     * Remove a product from the cart
     * @param CartPosition $item
     */
    public function removeProduct(CartPosition $item)
    {
        $item->findButton('Löschen')->click();
    }

    /**
     * Remove the voucher from the cart
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function removeVoucher()
    {
        $elements = Helper::findElements($this, ['removeVoucher']);
        $elements['removeVoucher']->click();
    }

    /**
     * Removes all products from the cart
     * @param CartPosition $items
     */
    public function emptyCart(CartPosition $items)
    {
        /** @var CartPosition $item */
        foreach ($items as $item) {
            $this->removeProduct($item);
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

    public function changeDispatchOrPaymentMethod($subject, $method)
    {
        $this->clickLink('Versandkosten');

        if ($subject === 'dispatch') {
            $elements = Helper::findElements($this, ['dispatchSelect']);

            if (empty($elements)) {
                throw new \Exception('Could not find dispatch select element on cart page.');
            }

            $elements['dispatchSelect']->selectOption($method);
        }
    }

    protected function verify(array $urlParameters)
    {
        $this->verifyResponse();
        $this->verifyPage();
    }

    /**
     * Checks the cart positions
     * Available properties are: number (required), name (required), quantity, itemPrice, sum
     * @param CartPosition $cartPositions
     * @param array $items
     * @throws \Exception
     */
    public function checkCartProducts(CartPosition $cartPositions, array $items)
    {
        if (count($items) !== count($cartPositions)) {
            throw new \Exception('The number of cart positions is incorrect.');
        }

        $items = Helper::floatArray($items, ['quantity', 'itemPrice', 'sum']);
        $result = Helper::assertElements($items, $cartPositions);

        if ($result !== true) {
            $messages = 'The following articles are wrong: \n';
            foreach ($result as $product) {
                $messages .= sprintf(
                    '%s - %s (%s is "%s", should be "%s") \n',
                    $product['properties']['number'],
                    $product['properties']['name'],
                    $product['result']['key'],
                    $product['result']['value'],
                    $product['result']['value2']
                );
            }
            throw new \Exception($messages);
        }
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @param string $language
     * @return bool
     * @throws \Exception
     */
    public function verifyPage($language = '')
    {
        if(!strpos($this->getHtml(), 'is--ctl-checkout is--act-cart')) {
            throw new \Exception('Could not verify page - expected to be on checkout/cart.');
        }

        return true;
    }

    /**
     * Proceeds to the confirmation page
     */
    public function proceedToOrderConfirmation()
    {
        if ($this->verifyPage()) {
            $this->clickLink('Zur Kasse');
        }

        $this->getPage('CheckoutConfirm')->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with login
     * @param string $eMail
     * @param string $password
     */
    public function proceedToOrderConfirmationWithLogin($eMail, $password)
    {
        if ($this->verifyPage()) {
            $this->clickLink('Zur Kasse');
        }

        $this->getPage('Account')->login($eMail, $password);
        $this->getPage('CheckoutConfirm')->verifyPage();
    }

    /**
     * Proceeds to the confirmation page with registration
     * @param array $data
     */
    public function proceedToOrderConfirmationWithRegistration(array $data)
    {
        if ($this->verifyPage()) {
            $this->clickLink('Zur Kasse');
        }

        $this->getPage('Account')->register($data);
    }

    public function resetCart()
    {
        try {
            $elements = Helper::findElements($this, ['articleDeleteButtons']);
            $elements[0]->click();
            if (count($elements) > 1) {
                $this->resetCart();
            }
        } catch (\Exception $ex) {
        }

        $this->open();
    }

    private function hasCartProductWithQuantity($number, $quantity)
    {
        $xPath = "//p[contains(text(), '" . $number . "')]//ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' row--product ')]//input[@name='sArticle' and @value='" . $quantity . "']";
        return $this->has('xpath', $xPath);
    }
}
