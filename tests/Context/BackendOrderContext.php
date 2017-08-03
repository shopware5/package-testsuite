<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert;
use Shopware\Page\Backend\OrderModule;
use Smalot\PdfParser\Parser;

class BackendOrderContext extends SubContext
{
    /**
     * @When I open the order from email :email
     * @param string $email
     */
    public function iOpenTheOrderFromEmail($email)
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->openOrderByEmail($email);
    }

    /**
     * @When I change the :type status to :status
     * @param string $type
     * @param string $status
     */
    public function iChangeTheOrderOrPaymentStatusTo($type, $status)
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->setStatusByType($type, $status);
    }

    /**
     * @Given I reload the status history
     */
    public function iReloadTheStatusHistory()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->reloadStatusHistory();
    }

    /**
     * @Then I should eventually see a generated invoice
     */
    public function iShouldEventuallySeeAGeneratedInvoice()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->waitForGeneratedInvoiceAppears();
    }

    /**
     * @When I click the email icon on the last generated document
     */
    public function iClickTheEmailIconOnTheLastGeneratedDocument()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->clickEmailIconOnLastGeneratedIcon();
    }

    /**
     * @When I filter the backend order list for shipping country :country
     * @param string $country
     */
    public function iFilterTheBackendOrderListForShippingCountry($country)
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->filterOrderListForShippingCountry($country);
    }

    /**
     * @Then I should see exactly :amount order in the order list
     * @param string $amount
     * @throws \Exception
     */
    public function iShouldSeeExactlyOneOrderInTheOrderList($amount)
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $actualAmount = $page->getNumberOfOrdersInOrderList();
        if ((int)$amount !== $actualAmount) {
            throw new \Exception(sprintf('Expected %s order, found %s.', $amount, $actualAmount));
        }
    }

    /**
     * @Given I sort the backend order list by order value ascendingly
     */
    public function iSortTheBackendOrderListByOrderValueAscendingly()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->sortOrderListByValue();
    }

    /**
     * @Then I should see the order from :email at the top of the order list
     * @param string $email
     * @throws \Exception
     */
    public function iShouldSeeTheOrderFromAtTheTopOfTheOrderList($email)
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $topmostOrder = $page->getTopmostOrderFromList();

        if (!strpos($topmostOrder->getHtml(), $email)) {
            throw new \Exception(sprintf('Expected order from %s would be at top of list.', $email));
        }
    }

    /**
     * @Then I should be able to send a notification to the customer
     */
    public function iShouldBeAbleToSendANotificationToTheCustomer()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');
        $page->sendCustomerNotificationMail();
    }

    /**
     * @Given the invoice should contain the following:
     * @param TableNode $content
     * @throws \Exception
     */
    public function theInvoiceShouldContain(TableNode $content)
    {
        $documentsPath = $this->getDocumentsDirectory();

        $documents = glob($documentsPath . '/*.pdf');
        switch (count($documents)) {
            case 0:
                throw new \Exception('Could not find generated PDF document.');
            case 1:
                break;
            default:
                echo "Warning - more than one document found. Is the test running on a clean SW installation?";
        }

        $pdfContent = $this->getPdfTextContent($documents[0]);

        foreach ($content->getHash() as $expectedString) {
            PHPUnit_Framework_Assert::assertContains($expectedString['content'], $pdfContent);
        }

        unlink($documents[0]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getDocumentsDirectory()
    {
        $documentsPath = getenv('base_path') . '/files/documents';

        if (!is_dir($documentsPath)) {
            throw new \Exception('Could not open document directory at ' . $documentsPath);
        }

        return $documentsPath;
    }

    /**
     * @param string $filepath
     * @return string
     * @throws \Exception
     */
    private function getPdfTextContent($filepath)
    {
        if (!is_file($filepath)) {
            throw new \Exception('Could not open file ' . $filepath);
        }

        return (new Parser())->parseFile($filepath)->getText();
    }
}