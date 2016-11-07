<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Element\ArticleEvaluation;

class DetailContext extends SubContext
{

    /**
     * @Given I am on the detail page for article with id :articleId
     * @When I go to the detail page for article with id :articleId
     */
    public function iAmOnTheDetailPageForArticleWithId($articleId)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->open(['articleId' => $articleId, 'number' => null]);
    }

    /**
     * @Given I am on the detail page for article with ordernumber :ordernumber
     * @When I go to the detail page for article with odernumber :ordernumber
     */
    public function iAmOnTheDetailPageForArticleWithOrdernumber($ordernumber)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Search');
        $page->open(['searchTerm' => $ordernumber]);
    }

    /**
     * @Given /^I am on the detail page for variant "(?P<number>[^"]*)" of article (?P<articleId>\d+)$/
     * @When /^I go to the detail page for variant "(?P<number>[^"]*)" of article (?P<articleId>\d+)$/
     */
    public function iAmOnTheDetailPageForVariantOfArticle($number, $articleId)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->open(['articleId' => $articleId, 'number' => $number]);
    }

    /**
     * @When I put the current article :quantity times into the basket
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->toBasket($quantity);
        $this->waitForText("Der Artikel wurde erfolgreich in den Warenkorb gelegt");
    }

    /**
     * @Given /^I should see an average customer evaluation of (?P<average>\d+) from following evaluations:$/
     */
    public function iShouldSeeAnAverageCustomerEvaluationOfFromFollowingEvaluations($average, TableNode $evaluations)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');

        /** @var ArticleEvaluation $articleEvaluations */
        $articleEvaluations = $this->getMultipleElement($page, 'ArticleEvaluation');
        $evaluations = $evaluations->getHash();

        $page->checkEvaluations($articleEvaluations, $average, $evaluations);
    }

    /**
     * @When /^I choose the following article configuration:$/
     */
    public function iChooseTheFollowingArticleConfiguration(TableNode $configuration)
    {
        $configuration = $configuration->getHash();
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->configure($configuration);
    }

    /**
     * @Then /^I can not select "([^"]*)" from "([^"]*)"$/
     */
    public function iCanNotSelectFrom($configuratorOption, $configuratorGroup)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup);
    }

    /**
     * @When /^I write an evaluation:$/
     */
    public function iWriteAnEvaluation(TableNode $data)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->writeEvaluation($data->getHash());
    }

    /**
     * @When /^the shop owner activates my latest evaluation$/
     * @When /^the shop owner activates my latest (\d+) evaluations$/
     */
    public function theShopOwnerActivateMyLatestEvaluation($limit = 1)
    {
        $sql = 'UPDATE `s_articles_vote` SET `active`= 1 ORDER BY id DESC LIMIT '.$limit;
        $this->getService('db')->exec($sql);
        $this->getSession()->reload();
    }

    /**
     * @Given /^I can select every (\d+)\. option of "([^"]*)" from "([^"]*)" to "([^"]*)"$/
     */
    public function iCanSelectEveryOptionOfFromTo($graduation, $select, $min, $max)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->checkSelect($select, $min, $max, $graduation);
    }

    /**
     * @When /^I submit the notification form with "([^"]*)"$/
     */
    public function iSubmitTheNotificationFormWith($email)
    {
        /** @var \Shopware\Tests\Mink\Page\Frontend\Detail $page */
        $page = $this->getPage('Detail');
        $page->submitNotification($email);
    }
}
