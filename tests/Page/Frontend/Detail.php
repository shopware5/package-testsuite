<?php

declare(strict_types=1);

namespace Shopware\Page\Frontend;

use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

class Detail extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $path = '/detail/index/sArticle/{articleId}?number={number}';

    /**
     * Puts the current article <quantity> times to basket
     *
     * @param int $quantity
     *
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

    /**
     * Checks if the amount and the corresponding graduated price are matching correctly
     *
     * @param string $graduatedprice
     *
     * @throws \Exception
     */
    public function checkGraduatedPrice($graduatedprice)
    {
        $builder = new FrontendXpathBuilder();

        $grPriceXpath = $builder
            ->child('span', ['~text' => $graduatedprice['amount'], 'and', '~class' => 'block-prices--quantity'], 1)
            ->ancestor('tr', [], 1)
            ->child('td', ['~text' => $graduatedprice['price'], 'and', '~class' => 'block-prices--cell'], 1)
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $grPriceXpath);
    }

    /**
     * Checks if the base price information is shown correctly
     *
     * @param string $entry
     *
     * @throws \Exception
     */
    public function checkBasePrice($entry)
    {
        $builder = new FrontendXpathBuilder();

        if ($entry['information'] === 'Einheit') {
            $spanXpath = $builder
                ->child('option', ['~text' => $entry['data']], 1)
                ->getXpath();
        } else {
            $spanXpath = $builder
                ->child('div', ['~text' => $entry['data']], 1)
                ->getXpath();
        }
        $this->waitForSelectorPresent('xpath', $spanXpath);
    }
}
