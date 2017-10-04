<?php

namespace Shopware\Context;

use Shopware\Page\Frontend\Detail;

class FrontendDetailContext extends SubContext
{
    /**
     * @When I choose the variant with the number :optionNumber
     * @param $optionNumber
     * @throws \Exception
     */
    public function iChooseTheVariantWithTheNumber($optionNumber)
    {
        $this->getDetailPage()->fillField("group[5]",$optionNumber);
    }

    /**
     * @Given I wait for the loading indicator to disappear
     * @throws \Exception
     */
    public function iWaitForTheLoadingIndicatorToDisappear()
    {
        $this->getDetailPage()->waitForOverlayToDisappear();
    }

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
}
