<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class NewArticleModule extends BackendModule
{
    private $priceRowAnchor = 'Beliebig';
    private $enterKeyNumber = 13;
    protected $moduleWindowTitle = 'Artikeldetails :';

    /**
     * Sets the price or other data for the article
     *
     * @param string $value
     * @param string $cellAnchor
     * @throws \Exception
     */
    public function setArticlePriceData($value, $cellAnchor, $inputName)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $cellElementXpath = $builder->child('span', ['@text' => $cellAnchor, 'and', '~class' => 'x-column-header-text'])->ancestor('div', [], 1)->getXpath();
        $cellElement = $window->find('xpath', $cellElementXpath);
        $this->assertNotNull($cellElement, $cellElementXpath);

        $priceCellPosition = $this->findPriceDataFieldPosition($cellElement, $cellAnchor);
        $this->setPriceData($value, $priceCellPosition, $inputName);
    }

    /**
     * Finds the field position for the price
     *
     * @param NodeElement $cellElement
     * @return int position
     * @throws \Exception
     */
    private function findPriceDataFieldPosition($cellElement, $anchor)
    {
        $builder = new BackendXpathBuilder();
        $positionIndex = 1;

        $cellParent = $cellElement->getParent()->getParent();

        /** @var NodeElement[] $cellList */
        $cells = $cellParent->findAll('xpath', $builder->child('div', ['~class' => 'x-column-header'])->getXpath());

        /** @var NodeElement $cell */
        foreach ($cells as $cell) {
            if ($cell->getText() === $anchor) {
                return $positionIndex;
            }
            ++$positionIndex;
        }
        throw new \Exception('Corresponding input field is missing.');
    }

    /**
     * Sets the price in the corresponding input field
     *
     * @param float $value
     * @param int $position
     * @param $inputName
     *
     * @throws \Exception
     */
    private function setPriceData($value, $position, $inputName)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $priceRowXpath = $builder->child('div', ['@text' => $this->priceRowAnchor])->ancestor('tr', [], 1)->getXpath();
        $row = $window->find('xpath', $priceRowXpath);

        $priceFieldXpath = $builder
            ->reset()
            ->child('td', [], $position)
            ->child('div', [], 1)
            ->getXpath();

        /** @var NodeElement $priceField */
        $priceField = $row->find('xpath', $priceFieldXpath);
        $this->waitForSelectorPresent('xpath', $priceFieldXpath);
        $priceField->click();

        $priceInputXpath = $builder->reset()->child('input', ['@name' => $inputName], 1)->getXpath();

        $this->waitForSelectorPresent('xpath', $priceInputXpath);

        $priceInput = $this->find('xpath', $priceInputXpath);
        $priceInput->setValue($value);
        $priceInput->keyPress($this->enterKeyNumber);
    }

    /**
     * Sets the text for the short description
     *
     * @param string $text
     */
    public function setDescription($text)
    {
        $this->getSession()->executeScript("tinymce.get()[0].setContent('" . $text . "');");
    }

    /**
     * Sets the basic information of the article
     *
     * @param array $data
     * @throws \Exception
     */
    public function setBasicData($data)
    {
        $window = $this->getModuleWindow(false);
        $this->fillExtJsForm($window, $data);
    }

    /**
     * Adds the category to the article
     *
     * @param string $name
     * @throws \Exception
     */
    public function addCategory($name)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $plusXpath = $builder
            ->child('div', ['@text' => $name])
            ->ancestor('tr', [], 1)
            ->descendant('td', [], 2)
            ->descendant('img')
            ->getXpath();

        $plus = $window->find('xpath', $plusXpath);
        $this->assertNotNull($plus, $plusXpath);
        $plus->click();
    }

    /**
     * Checks if the category is connected to the article correctly
     *
     * @param string $name
     * @param string $area
     * @throws \Exception
     */
    public function checkAddedCategory($name, $area)
    {
        $window = $this->getModuleWindow(false);
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
     * Saves the article
     *
     */
    public function saveArticle()
    {
        $button = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Artikel speichern'));
        $button->click();
    }

}
