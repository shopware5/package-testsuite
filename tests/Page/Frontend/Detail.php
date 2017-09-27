<?php

namespace Shopware\Page\Frontend;

use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Detail extends ContextAwarePage
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}?number={number}';

    /**
     * Puts the current article <quantity> times to basket
     *
     * @param int $quantity
     * @throws ElementNotFoundException
     */
    public function toBasket($quantity = 1)
    {
        $this->fillField('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');
    }

    /**
     * Checks if the variants are applied in the frontend correctly
     *
     * @throws \Exception
     */
    public function waitForOverlayToDisappear()
    {
        $builder = new FrontendXpathBuilder();

        $overlayXpath = $builder
            ->child('div', ['~class' => 'js--overlay'], 1)
            ->getXpath();

        $this->waitForSelectorNotPresent('xpath', $overlayXpath);
    }
}
