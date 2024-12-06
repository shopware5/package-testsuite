<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Exception;
use Shopware\Page\Frontend\Detail;
use Shopware\Page\Frontend\Search;

class FrontendDetailContext extends SubContext
{
    /**
     * @When I choose the variant with the number :optionNumber
     *
     * @throws Exception
     */
    public function iChooseTheVariantWithTheNumber(string $optionNumber): void
    {
        $this->getDetailPage()->fillField('group[5]', $optionNumber);
    }

    /**
     * @Given I wait for the loading indicator to disappear
     *
     * @throws Exception
     */
    public function iWaitForTheLoadingIndicatorToDisappear(): void
    {
        $this->getDetailPage()->waitForOverlayToDisappear();
    }

    /**
     * @Given I am on the detail page for article with ordernumber :ordernumber
     *
     * @throws Exception
     */
    public function iAmOnTheDetailPageForArticleWithOrdernumber(string $ordernumber): void
    {
        $this->getSearchPage()->open(['searchTerm' => $ordernumber]);
    }

    /**
     * @When I put the current article :quantity times into the basket
     *
     * @throws Exception
     */
    public function iPutTheArticleTimesIntoTheBasket(string $quantity): void
    {
        $this->getDetailPage()->toBasket($quantity);
        $this->waitForText('Der Artikel wurde erfolgreich in den Warenkorb gelegt');
    }

    /**
     * @Then I should see the following graduated prices:
     *
     * @throws Exception
     */
    public function iShouldSeeTheFollowingGraduatedPrices(TableNode $table): void
    {
        foreach ($table->getHash() as $graduatedPrice) {
            $this->getDetailPage()->checkGraduatedPrice($graduatedPrice);
        }
    }

    /**
     * @Given I should see the base price information:
     *
     * @throws Exception
     */
    public function iShouldSeeTheBasePriceInformation(TableNode $table): void
    {
        foreach ($table->getHash() as $entry) {
            $this->getDetailPage()->checkBasePrice($entry);
        }
    }

    /**
     * @throws Exception
     */
    private function getDetailPage(): Detail
    {
        return $this->getValidPage(Detail::class);
    }

    /**
     * @throws Exception
     */
    private function getSearchPage(): Search
    {
        return $this->getValidPage(Search::class);
    }
}
