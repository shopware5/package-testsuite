<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Shopware\Page\Backend\OrderModule;
use Smalot\PdfParser\Parser;

class BackendOrderContext extends PageObjectContext
{
    /**
     * @When I open the order from email :email
     *
     * @param string $email
     *
     * @throws \RuntimeException
     */
    public function iOpenTheOrderFromEmail($email)
    {
        $this->getModulePage()->openOrderByEmail($email);
    }

    /**
     * @When I change the :type status to :status
     *
     * @param string $type
     * @param string $status
     */
    public function iChangeTheOrderOrPaymentStatusTo($type, $status)
    {
        $this->getModulePage()->setStatusByType($type, $status);
    }

    /**
     * @Given I reload the status history
     */
    public function iReloadTheStatusHistory()
    {
        $this->getModulePage()->reloadStatusHistory();
    }

    /**
     * @When I click the email icon on the last generated document
     */
    public function iClickTheEmailIconOnTheLastGeneratedDocument()
    {
        $this->getModulePage()->clickEmailIconOnLastGeneratedIcon();
    }

    /**
     * @When I filter the backend order list for shipping country :country
     *
     * @param string $country
     */
    public function iFilterTheBackendOrderListForShippingCountry($country)
    {
        $this->getModulePage()->filterOrderListForShippingCountry($country);
    }

    /**
     * @Then I should see exactly :amount order in the order list
     *
     * @param string $amount
     *
     * @throws \Exception
     */
    public function iShouldSeeExactlyOneOrderInTheOrderList($amount)
    {
        $actualAmount = $this->getModulePage()->getNumberOfOrdersInOrderList();
        if ((int) $amount !== $actualAmount) {
            throw new \Exception(sprintf('Expected %s order, found %s.', $amount, $actualAmount));
        }
    }

    /**
     * @Given I sort the backend order list by order value ascendingly
     */
    public function iSortTheBackendOrderListByOrderValueAscendingly()
    {
        $this->getModulePage()->sortOrderListByValue();
    }

    /**
     * @Then I should see the order from :email at the top of the order list
     *
     * @param string $email
     *
     * @throws \Exception
     */
    public function iShouldSeeTheOrderFromAtTheTopOfTheOrderList($email)
    {
        $topmostOrder = $this->getModulePage()->getTopmostOrderFromList();

        if (!strpos($topmostOrder->getHtml(), $email)) {
            throw new \Exception(sprintf('Expected order from %s would be at top of list.', $email));
        }
    }

    /**
     * @Then I should be able to send a notification to the customer
     */
    public function iShouldBeAbleToSendANotificationToTheCustomer()
    {
        $this->getModulePage()->sendCustomerNotificationMail();
    }

    /**
     * @Given the invoice should contain the following:
     *
     * @throws \Exception
     */
    public function theInvoiceShouldContain(TableNode $content)
    {
        // Allow time for the invoice to be generated
        sleep(3);

        $documentsPath = $this->getDocumentsDirectory();

        $documents = glob($documentsPath . '/*.pdf');
        if (empty($documents)) {
            throw new \Exception('Could not find generated PDF document.');
        }

        $pdfContent = $this->getPdfTextContent($documents[0]);

        foreach ($content->getHash() as $expectedString) {
            Assert::assertStringContainsString($expectedString['content'], $pdfContent);
        }

        unlink($documents[0]);
    }

    /**
     * @return OrderModule
     */
    private function getModulePage()
    {
        /** @var OrderModule $page */
        $page = $this->getPage('OrderModule');

        return $page;
    }

    /**
     * @throws \Exception
     *
     * @return string
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
     *
     * @throws \Exception
     *
     * @return string
     */
    private function getPdfTextContent($filepath)
    {
        if (!is_file($filepath)) {
            throw new \Exception('Could not open file ' . $filepath);
        }

        return (new Parser())->parseFile($filepath)->getText();
    }
}
