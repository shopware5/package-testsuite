<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Page\Frontend\Detail;
use Shopware\Page\Frontend\Search;

class FrontendDetailContext extends SubContext
{
    /**
     * @return Detail
     * @throws \Exception
     */
    private function getDetailPage()
    {
        /** @var Detail $page */
        $page = $this->getPage('Detail');
        if ($page === null) {
            throw new \RuntimeException('Page is not defined.');
        }
        return $page;
    }


    /**
     * @return Search
     * @throws \Exception
     */
    private function getSearchPage()
    {
        /** @var Search $page */
        $page = $this->getPage('Search');
        if ($page === null) {
            throw new \RuntimeException('Page is not defined.');
        }
        return $page;
    }

    /**
     * @When I choose the variant with the number :optionNumber
     *
     * @param $optionNumber
     * @throws \Exception
     */
    public function iChooseTheVariantWithTheNumber($optionNumber)
    {
        $this->getDetailPage()->fillField('group[5]', $optionNumber);
    }

    /**
     * @Given I wait for the loading indicator to disappear
     *
     * @throws \Exception
     */
    public function iWaitForTheLoadingIndicatorToDisappear()
    {
        $this->getDetailPage()->waitForOverlayToDisappear();
    }

    /**
     * @Given I am on the detail page for article with ordernumber :ordernumber
     *
     * @param string $ordernumber
     * @throws \Exception
     */
    public function iAmOnTheDetailPageForArticleWithOrdernumber($ordernumber)
    {
        $this->getSearchPage()->open(['searchTerm' => $ordernumber]);
    }

    /**
     * @When I put the current article :quantity times into the basket
     *
     * @param string $quantity
     * @throws \Exception
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity)
    {
        $this->getDetailPage()->toBasket($quantity);
        $this->waitForText('Der Artikel wurde erfolgreich in den Warenkorb gelegt');
    }


    /**
     * @Then I should see the following graduated prices:
     *
     * @param TableNode $table
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingGraduatedPrices(TableNode $table)
    {
        $data = $table->getHash();

        foreach ($data as $graduatedprice) {
            $this->getDetailPage()->checkGraduatedPrice($graduatedprice);
        }
    }
}
