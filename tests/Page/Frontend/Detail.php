<?php

namespace Shopware\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Detail extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}?number={number}';

    /**
     * Puts the current article <quantity> times to basket
     *
     * @param int $quantity
     */
    public function toBasket($quantity = 1)
    {
        $this->fillField('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');
    }
}
