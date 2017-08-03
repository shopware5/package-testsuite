<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class ArticleModule extends BackendModule
{
    /** @var string */
    protected $moduleWindowTitle = 'Artikeldetails : Neuer Artikel';

    private $priceCellLabel = 'Preis';
    private $priceRowAnchor = 'Beliebig';

    /**
     * Clicks a specific element with extended title
     *
     * @param string $elementName Name of the element
     **/
    public function clickOnExtendedElement($elementName)
    {
        $builder = new BackendXpathBuilder();

        $elementXpath = $builder->child('span', ['@text' => $elementName, 'and', '~class' => 'shortcut'])->ancestor('div', [], 1)->getXpath();

        $element = $this->find('xpath', $elementXpath);
        $this->assertNotNull($element, $elementXpath);
        $element->click();
    }

    /**
     * Fills in a checkbox
     * @param $price
     */
    public function setPriceForCustomerGroup($price)
    {
        $window = $this->getModuleWindow();
        $builder = new BackendXpathBuilder();

        $cellElementXpath = $builder->child('span', ['@text' => $this->priceCellLabel, 'and', '~class' => 'x-column-header-text'])->ancestor('div', [], 1)->getXpath();
        $cellElement = $window->find('xpath', $cellElementXpath);
        $this->assertNotNull($cellElement, $cellElementXpath);

        $priceCellPosition = $this->findPriceFieldPosition($cellElement);
        $this->setPrice($window, $price, $priceCellPosition);
    }

    /**
     * Finds the field position for the price
     * @param NodeElement $cellElement
     * @return int position
     */
    private function findPriceFieldPosition($cellElement)
    {
        $builder = new BackendXpathBuilder();
        $positionIndex = 1;

        $cellParent = $cellElement->getParent()->getParent();

        /** @var NodeElement[] $cellList */
        $cells = $cellParent->findAll('xpath', $builder->child('div', ['~class' => 'x-column-header'])->getXpath());

        /** @var NodeElement $cell */
        foreach ($cells as $cell) {

            if ($cell->getText() === $this->priceCellLabel) {
                return $positionIndex;
            }
            ++$positionIndex;
        }
    }

    /**
     * Sets the price eventually.
     * @param NodeElement $window
     * @param float $price
     * @param int $position
     */
    private function setPrice($window, $price, $position)
    {
        $builder = new BackendXpathBuilder();

        $priceRowXpath = $builder->child('div', ['@text' => $this->priceRowAnchor])->ancestor('tr', [], 1)->getXpath();
        $row = $window->find('xpath', $priceRowXpath);
        $this->assertNotNull($row, $priceRowXpath);

        $priceFieldXpath = $builder
            ->reset()
            ->child('td', [], $position)
            ->child('div', [], 1)
            ->getXpath();


        /** @var NodeElement $priceField */
        $priceField = $row->find('xpath', $priceFieldXpath);
        $this->assertNotNull($priceField, $priceFieldXpath);
        $priceField->click();

        $priceInputXpath = $builder->reset()->child('input', ['@name' => 'price'], 1)->getXpath();

        $this->waitForSelectorPresent('xpath', $priceInputXpath);

        $priceInput = $this->find('xpath', $priceInputXpath);
        $this->assertNotNull($priceInput, $priceInputXpath);
        $priceInput->setValue($price);
        $priceInput->keyPress(13);

    }

    /**
     * Sets the text for the short description
     * @param string $text
     */
    public function setDescription($text)
    {
        $this->getSession()->executeScript("tinymce.get()[0].setContent('" . $text . "');");
    }

    /**
     * Sets the text for the short description
     * @param string $text
     */
    public function uploadArticleImage($text)
    {
        $this->getSession()->executeScript("tinymce.get()[0].setContent('" . $text . "');");
    }

    /**
     * Sets the text for the short description
     * @param $data
     */
    public function setBasicData($data)
    {
        $window = $this->getModuleWindow();
        $this->fillExtJsForm($window, $data);
    }

    /**
     * Sets the text for the short description
     * @param $icon
     * @param $name
     */
    public function addCategory($icon, $name)
    {
        $window = $this->getModuleWindow();
        $builder = new BackendXpathBuilder();

        $plusXpath = $builder
            ->child('div', ['@text' => 'Unterkategorie'])
            ->ancestor('tr', [], 1)
            ->descendant('td', [], 2)
            ->descendant('img')
            ->getXpath();

        $plus = $window->find('xpath', $plusXpath);
        $this->assertNotNull($plus, $plusXpath);
        $plus->click();
    }

    /**
     * Sets the text for the short description
     * @param $name
     * @param $area
     */
    public function checkAddedCategory($name, $area)
    {
        $window = $this->getModuleWindow();
        $builder = new BackendXpathBuilder();

        $plusXpath = $builder
            ->child('div', ['@text' => $name])
            ->ancestor('div', ['~class' => 'x-panel'], 1)
            ->descendant('span', ['@text' => $area], 1)
            ->getXpath();

        $plus = $window->find('xpath', $plusXpath);
        $this->assertNotNull($plus, $plusXpath);
    }


    /**
     * Sets the text for the short description
     * @param $name
     */
    public function chooseShopForPreview($name)
    {
        $window = $this->getModuleWindow();
        $builder = new BackendXpathBuilder();

        $comboboxXpath = $builder
            ->child('label', ['@text' => 'Artikel-Vorschau:'])
            ->ancestor('table', ['~class' => 'x-form-item'], 1)
            ->getXpath();

        //$combobox = $window->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel($name));
        $this->fillCombobox($this->find('xpath',$comboboxXpath), 'Demo shop');
    }


    public function startPreview()
    {
        $window = $this->getModuleWindow();
        $builder = new BackendXpathBuilder();

        $previewXpath = $builder
            ->child('span', ['@text' => 'Vorschau'])
            ->ancestor('button', [], 1)
            ->getXpath();

        $previewButton = $window->find('xpath', $previewXpath);
        $this->assertNotNull($previewButton, $previewXpath);
        $previewButton->click();
    }
}