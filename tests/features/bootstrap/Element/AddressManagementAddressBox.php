<?php

namespace Shopware\Tests\Mink\Element;

/**
 * Element: AddressManagementAddressBox
 * Location: Account address boxes
 *
 * Available retrievable properties:
 * -
 */
class AddressManagementAddressBox extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.address--box'];

    protected $xPath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' address-editor--body ')]";

    public function getXpath()
    {
        return $this->xPath;
    }

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'form' => 'form[name="frmAddresses"]',
            'title' => '.panel--title',
            'company' => '.address--company',
            'address' => '.address--address',
            'salutation' => '.address--salutation',
            'customerTitle' => '.address--title',
            'firstname' => '.address--firstname',
            'lastname' => '.address--lastname',
            'street' => '.address--street',
            'addLineOne' => '.address--additional-one',
            'addLineTwo' => '.address--additional-two',
            'zipcode' => '.address--zipcode',
            'city' => '.address--city',
            'stateName' => '.address--statename',
            'countryName' => '.address--countryname',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeLink' => ['de' => 'Bearbeiten', 'en' => 'Edit'],
            'deleteLink' => ['de' => 'Löschen', 'en' => 'Delete'],
            'saveAddressButton' => ['de' => 'Adresse speichern', 'en' => 'Save address'],
            'setDefaultShippingButton' => ['de' => 'Als Standard-Lieferadresse verwenden', 'en' => 'Set as default shipping address'],
            'setDefaultBillingButton' => ['de' => 'Als Standard-Rechnungsadresse verwenden', 'en' => 'Set as default billing address'],
        ];
    }

    public function hasTitle($title)
    {
        if ($this->has('css', $this->getCssSelectors()['title'])) {
            return $this->getTitleProperty() === $title;
        }

        return false;
    }
}
