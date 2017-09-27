<?php

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\GridView\GridView;
use Shopware\Element\Backend\GridView\GridViewRow;

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
     */
    public function openOrderByEmail($email)
    {
        $orderRow = $this->getOrderListGridView()->getRowByContent($email);
        $orderRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Set order or payment status
     *
     * @param string $type
     * @param string $status
     */
    public function setStatusByType($type, $status)
    {
        $label = strtolower($type) === 'order' ? 'Bestellstatus:' : 'Zahlungsstatus:';

        $statusCombobox = $this->getEditorWindow()->getCombobox($label);
        $statusCombobox->setValue($status);
    }

    /**
     * Send a notification email to the customer using the email window opened after saving after a status history change
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
     */
    public function reloadStatusHistory()
    {
        $gridView = $this->getEditorWindow()->getGridView('Benutzer');
        $gridView->reload();
    }

    /**
     * Click the email icon on the topmost generated document
     */
    public function clickEmailIconOnLastGeneratedIcon()
    {
        $row = $this->getLastGeneratedDocumentGridRow();
        $row->clickActionIcon('sprite-mail-send');
    }

    /**
     * Filter the backend order list by shipping country
     *
     * @param string $country
     */
    public function filterOrderListForShippingCountry($country)
    {
        $this->getModuleWindow()->getCombobox('Lieferland:')->setValue($country);
        $this->findButton('Ausführen')->click();

        // Wait for the list to reload automatically
        sleep(3);
    }

    /**
     * Get number of orders in backend list
     *
     * @return integer
     */
    public function getNumberOfOrdersInOrderList()
    {
        $gridView = $this->getOrderListGridView();
        return count($gridView->getRows());
    }

    /**
     * Sort backend order list by order value (ascendingly)
     */
    public function sortOrderListByValue()
    {
        sleep(3);
        $this->getOrderListGridView()->sortBy('Betrag');
    }

    /**
     * Get the topmost order from the backend listing
     *
     * @return GridViewRow
     */
    public function getTopmostOrderFromList()
    {
        return $this->getOrderListGridView()->getRows()[0];
    }

    /**
     * Get the latest generated document grid row
     *
     * @return GridViewRow
     */
    private function getLastGeneratedDocumentGridRow()
    {
        return $this->getEditorWindow()->getGridView('Betrag')->getRowByContent('Rechnung');
    }

    /**
     * Get main order list grid view
     *
     * @return GridView
     */
    private function getOrderListGridView()
    {
        return $this->getModuleWindow()->getGridView('Bestellnummer');
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditorWindow($exactMatch = false)
    {
        return parent::getEditorWindow($exactMatch);
    }
}
