<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Shopware\Page\Backend\OrderModule;
use Smalot\PdfParser\Parser;

class BackendOrderContext extends SubContext
{
    /**
     * @When I open the order from email :email
     *
     * @throws \RuntimeException
     */
    public function iOpenTheOrderFromEmail(string $email): void
    {
        $this->getModulePage()->openOrderByEmail($email);
    }

    /**
     * @When I change the :type status to :status
     */
    public function iChangeTheOrderOrPaymentStatusTo(string $type, string $status): void
    {
        $this->getModulePage()->setStatusByType($type, $status);
    }

    /**
     * @Given I reload the status history
     */
    public function iReloadTheStatusHistory(): void
    {
        $this->getModulePage()->reloadStatusHistory();
    }

    /**
     * @When I click the email icon on the last generated document
     */
    public function iClickTheEmailIconOnTheLastGeneratedDocument(): void
    {
        $this->getModulePage()->clickEmailIconOnLastGeneratedIcon();
    }

    /**
     * @When I filter the backend order list for shipping country :country
     */
    public function iFilterTheBackendOrderListForShippingCountry(string $country): void
    {
        $this->getModulePage()->filterOrderListForShippingCountry($country);
    }

    /**
     * @Then I should see exactly :amount order in the order list
     *
     * @throws \Exception
     */
    public function iShouldSeeExactlyOneOrderInTheOrderList(string $amount): void
    {
        $actualAmount = $this->getModulePage()->getNumberOfOrdersInOrderList();
        if ((int) $amount !== $actualAmount) {
            throw new \Exception(sprintf('Expected %s order, found %s.', $amount, $actualAmount));
        }
    }

    /**
     * @Given I sort the backend order list by order value ascendingly
     */
    public function iSortTheBackendOrderListByOrderValueAscendingly(): void
    {
        $this->getModulePage()->sortOrderListByValue();
    }

    /**
     * @Then I should see the order from :email at the top of the order list
     *
     * @throws \Exception
     */
    public function iShouldSeeTheOrderFromAtTheTopOfTheOrderList(string $email): void
    {
        $topmostOrder = $this->getModulePage()->getTopmostOrderFromList();

        if (!strpos($topmostOrder->getHtml(), $email)) {
            throw new \Exception(sprintf('Expected order from %s would be at top of list.', $email));
        }
    }

    /**
     * @Then I should be able to send a notification to the customer
     */
    public function iShouldBeAbleToSendANotificationToTheCustomer(): void
    {
        $this->getModulePage()->sendCustomerNotificationMail();
    }

    /**
     * @Given the invoice should contain the following:
     *
     * @throws \Exception
     */
    public function theInvoiceShouldContain(TableNode $content): void
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

    private function getModulePage(): OrderModule
    {
        return $this->getValidPage('OrderModule', OrderModule::class);
    }

    /**
     * @throws \Exception
     */
    private function getDocumentsDirectory(): string
    {
        $documentsPath = getenv('base_path') . '/files/documents';

        if (!is_dir($documentsPath)) {
            throw new \Exception('Could not open document directory at ' . $documentsPath);
        }

        return $documentsPath;
    }

    /**
     *@throws \Exception
     */
    private function getPdfTextContent(string $filepath): string
    {
        if (!is_file($filepath)) {
            throw new \Exception('Could not open file ' . $filepath);
        }

        return (new Parser())->parseFile($filepath)->getText();
    }
}
