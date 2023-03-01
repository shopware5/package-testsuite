<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\GridView\GridView;
use Shopware\Element\Backend\GridView\GridViewRow;
use Shopware\Element\Backend\Window;

class OrderModule extends BackendModule
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=Order';

    protected string $moduleWindowTitle = 'Bestellungen';

    protected string $editorWindowTitle = 'Bestellungs-Details:';

    /**
     * Open an order by email
     */
    public function openOrderByEmail(string $email): void
    {
        $orderRow = $this->getOrderListGridView()->getRowByContent($email);
        $orderRow->clickActionIcon('sprite-pencil');
    }

    /**
     * Set order or payment status
     */
    public function setStatusByType(string $type, string $status): void
    {
        $label = strtolower($type) === 'order' ? 'Bestellstatus:' : 'Zahlungsstatus:';

        $statusCombobox = $this->getEditorWindow(false)->getCombobox($label);
        $statusCombobox->setValue($status);
    }

    /**
     * Send a notification email to the customer using the email window opened after saving after a status history change
     */
    public function sendCustomerNotificationMail(): void
    {
        $this->waitForText('E-Mail an den Kunden senden');

        $buttonXpath = BackendXpathBuilder::getButtonXpathByLabel('E-Mail senden');
        $this->waitForSelectorPresent('xpath', $buttonXpath);
        $button = $this->find('xpath', $buttonXpath);
        $button->click();
        $this->waitForSelectorNotPresent('xpath', $buttonXpath);
    }

    /**
     * Reload the status history tab
     */
    public function reloadStatusHistory(): void
    {
        $gridView = $this->getEditorWindow(false)->getGridView('Benutzer');
        $gridView->reload();
    }

    /**
     * Filter the backend order list by shipping country
     */
    public function filterOrderListForShippingCountry(string $country): void
    {
        $this->getModuleWindow()->getCombobox('Lieferland:')->setValue($country);
        $this->findButton('Ausführen')->click();

        // Wait for the list to reload automatically
        sleep(3);
    }

    /**
     * Get number of orders in backend list
     */
    public function getNumberOfOrdersInOrderList(): int
    {
        $gridView = $this->getOrderListGridView();

        return \count($gridView->getRows());
    }

    /**
     * Sort backend order list by order value (ascendingly)
     */
    public function sortOrderListByValue(): void
    {
        sleep(3);
        $this->getOrderListGridView()->sortBy('Betrag');
    }

    /**
     * Get the topmost order from the backend listing
     */
    public function getTopmostOrderFromList(): GridViewRow
    {
        return $this->getOrderListGridView()->getFirstRow();
    }

    /**
     * Get main order list grid view
     */
    private function getOrderListGridView(): GridView
    {
        return $this->getModuleWindow()->getGridView('Bestellnummer');
    }
}
