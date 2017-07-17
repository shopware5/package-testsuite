<?php

namespace Shopware\Context;

class DetailContext extends SubContext
{
    /**
     * @Given I am on the detail page for article with ordernumber :ordernumber
     * @param string $ordernumber
     */
    public function iAmOnTheDetailPageForArticleWithOrdernumber($ordernumber)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Search');
        $page->open(['searchTerm' => $ordernumber]);
    }

    /**
     * @When I put the current article :quantity times into the basket
     * @param string $quantity
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->toBasket($quantity);
        $this->waitForText("Der Artikel wurde erfolgreich in den Warenkorb gelegt");
    }
}
