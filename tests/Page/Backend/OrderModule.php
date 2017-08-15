<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class OrderModule extends BackendModule
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Order';

    /**
     * @var string
     */
    protected $moduleWindowTitle = 'Bestellungen';

    /**
     * @var string
     */
    protected $editorWindowTitle = 'Bestellungs-Details:';

    /**
     * Open an order by email
     *
     * @param string $email
     * @throws \Exception
     */
    public function openOrderByEmail($email)
    {
        $window = $this->getModuleWindow();

        $editIconXpath = BackendXpathBuilder::create()
            ->child('a')
            ->contains($email)
            ->ancestor('tr', ['~class' => 'x-grid-row'])
            ->descendant('img', ['~class' => 'sprite-pencil'])
            ->getXpath();

        $this->waitForXpathElementPresent($editIconXpath);
        $editIcon = $window->find('xpath', $editIconXpath);
        $editIcon->click();
    }

    /**
     * Set order or payment status
     *
     * @param string $type
     * @param string $status
     * @throws \Exception
     */
    public function setStatusByType($type, $status)
    {
        $label = strtolower($type) === 'order' ? 'Bestellstatus:' : 'Zahlungsstatus:';

        $containerXpath = BackendXpathBuilder::create()
            ->child('span')
            ->contains('Bestellung bearbeiten')
            ->ancestor('div', ['~class' => 'x-panel-default'])
            ->getXpath();
        $this->waitForSelectorPresent('xpath', $containerXpath);
        $container = $this->find('xpath', $containerXpath);

        $statusComboboxXpath = BackendXpathBuilder::getComboboxXpathByLabel($label);
        $statusCombobox = $container->find('xpath', $statusComboboxXpath);

        $this->fillCombobox($statusCombobox, $status);
    }

    /**
     * Send a notification email to the customer using the email window opened after saving after a status history change
     *
     * @throws \Exception
     */
    public function sendCustomerNotificationMail()
    {
        $this->waitForText("E-Mail an den Kunden senden");

        $buttonXpath = BackendXpathBuilder::getButtonXpathByLabel('E-Mail senden');
        $this->waitForSelectorPresent('xpath', $buttonXpath);
        $button = $this->find('xpath', $buttonXpath);
        $button->click();

        sleep(4);
    }

    /**
     * Reload the status history tab
     *
     * @throws \Exception
     */
    public function reloadStatusHistory()
    {
        $window = $this->getEditorWindow(false);

        $loadingButtonXpath = BackendXpathBuilder::create()
            ->child('div', ['~class' => 'x-order-history-grid'])
            ->descendant('span', ['~class' => 'x-tbar-loading'])
            ->getXpath();

        $window->find('xpath', $loadingButtonXpath)->click();
    }

    /**
     * Wait for a generated invoice to appear
     *
     * @throws \Exception
     */
    public function waitForGeneratedInvoiceAppears()
    {
        $row = $this->getLastGeneratedDocumentGridRow();

        if (!strpos($row->getHtml(), 'Rechnung')) {
            throw new \Exception('Could not find generated invoice');
        }
    }

    /**
     * Filter the backend order list by shipping country
     *
     * @param string $country
     * @throws \Exception
     */
    public function filterOrderListForShippingCountry($country)
    {
        $window = $this->getModuleWindow();
        $this->fillExtJsForm($window, [['label' => 'Lieferland:', 'value' => $country, 'type' => 'combobox']]);
        $this->findButton('Ausführen')->click();
    }

    /**
     * Get number of orders in backend list
     *
     * @return integer
     * @throws \Exception
     */
    public function getNumberOfOrdersInOrderList()
    {
        return count($this->getModuleWindow()->findAll('xpath',
            BackendXpathBuilder::create()
                ->child('tr', ['~class' => 'x-grid-row'])
                ->getXpath()
        ));
    }

    /**
     * Sort backend order list by order value (ascendingly)
     *
     * @throws \Exception
     */
    public function sortOrderListByValue()
    {
        $tableHeaderXpath = BackendXpathBuilder::create()
            ->child('span', ['~class' => 'x-column-header-text'])
            ->contains('Betrag')
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $tableHeaderXpath);
        $this->find('xpath', $tableHeaderXpath)->click();
    }

    /**
     * Get the topmost order from the backend listing
     *
     * @return NodeElement|null
     * @throws \Exception
     */
    public function getTopmostOrderFromList()
    {
        return $this->getModuleWindow()->find('xpath',
            BackendXpathBuilder::create()
                ->child('tr', ['~class' => 'x-grid-row'])
                ->getXpath()
        );
    }

    /**
     * Get the latest generated document grid row
     *
     * @return NodeElement|null
     * @throws \Exception
     */
    private function getLastGeneratedDocumentGridRow()
    {
        $tableRowXpath = BackendXpathBuilder::create()
            ->child('div', ['~class' => 'x-document-grid'])
            ->descendant('tr', ['~class' => 'x-grid-row'])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $tableRowXpath);

        return $this->getEditorWindow(false)->find('xpath', $tableRowXpath);
    }
}
