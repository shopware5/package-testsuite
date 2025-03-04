<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Exception;
use RuntimeException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class NewArticleModule extends BackendModule
{
    protected string $moduleWindowTitle = 'Artikeldetails :';

    private string $priceRowAnchor = 'Beliebig';

    /**
     * Sets the price or other data for the article
     *
     * @throws Exception
     */
    public function setArticlePriceData(string $value, string $cellAnchor, string $inputName): void
    {
        $window = $this->getModuleWindow(false);
        $cellElementXpath = (new BackendXpathBuilder())
            ->child('span', ['@text' => $cellAnchor, 'and', '~class' => 'x-column-header-text'])
            ->ancestor('div', [], 1)
            ->getXpath();
        $this->waitForSelectorPresent('xpath', $cellElementXpath);
        $this->waitForSelectorVisible('xpath', $cellElementXpath);
        $cellElement = $window->find('xpath', $cellElementXpath);

        $priceCellPosition = $this->findPriceDataFieldPosition($cellElement, $cellAnchor);
        $this->setPriceData($value, $priceCellPosition, $inputName);
    }

    /**
     * Sets the text for the short description
     */
    public function setDescription(string $text): void
    {
        $this->getDriver()->executeScript("tinymce.get()[0].setContent('" . $text . "');");
    }

    /**
     * Sets the basic information of the article
     *
     * @throws Exception
     */
    public function setBasicData(array $data): void
    {
        $window = $this->getModuleWindow(false);
        $this->fillExtJsForm($window, $data);
    }

    /**
     * Adds the category to the article
     *
     * @throws Exception
     */
    public function addCategory(string $name): void
    {
        $window = $this->getModuleWindow(false);
        $plusXpath = (new BackendXpathBuilder())
            ->child('div', ['@text' => $name])
            ->ancestor('tr', [], 1)
            ->descendant('td', [], 2)
            ->descendant('img')
            ->getXpath();

        $plus = $window->find('xpath', $plusXpath);
        $plus->click();
    }

    /**
     * Checks if the category is connected to the article correctly
     *
     * @throws Exception
     */
    public function checkAddedCategory(string $name, string $area): void
    {
        $window = $this->getModuleWindow(false);
        $plusXpath = (new BackendXpathBuilder())
            ->child('div', ['@text' => $name])
            ->ancestor('div', ['~class' => 'x-panel'], 1)
            ->descendant('span', ['@text' => $area], 1)
            ->getXpath();

        $window->find('xpath', $plusXpath);
    }

    /**
     * Saves the article
     */
    public function saveArticle(): void
    {
        $button = $this->waitForSelectorPresent('xpath', BackendXpathBuilder::getButtonXpathByLabel('Artikel speichern'));
        $button->click();
    }

    /**
     * Finds the field position for the price
     *
     * @throws Exception
     *
     * @return int position
     */
    private function findPriceDataFieldPosition(NodeElement $cellElement, string $anchor): int
    {
        $builder = new BackendXpathBuilder();
        $positionIndex = 1;

        $cellParent = $cellElement->getParent()->getParent();
        $this->waitForSelectorPresent('xpath', $cellParent->getXpath());
        $this->waitForSelectorVisible('xpath', $cellParent->getXpath());

        $cells = $cellParent->findAll('xpath', $builder->child('div', ['~class' => 'x-column-header'])->getXpath());

        foreach ($cells as $cell) {
            if ($cell->getText() === $anchor) {
                return $positionIndex;
            }
            ++$positionIndex;
        }
        throw new Exception('Corresponding input field is missing.');
    }

    /**
     * Sets the price in the corresponding input field
     *
     * @throws Exception
     */
    private function setPriceData(string $value, int $position, string $inputName): void
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $priceRowXpath = $builder->child('div', ['@text' => $this->priceRowAnchor])->ancestor('tr', [], 1)->getXpath();

        $this->waitForSelectorPresent('xpath', $priceRowXpath);
        $this->waitForSelectorVisible('xpath', $priceRowXpath);
        $row = $window->find('xpath', $priceRowXpath);

        $priceFieldXpath = $builder
            ->reset()
            ->child('td', [], $position)
            ->child('div', [], 1)
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $priceFieldXpath);
        $this->waitForSelectorVisible('xpath', $priceFieldXpath);
        $priceField = $row->find('xpath', $priceFieldXpath);
        if (!$priceField instanceof NodeElement) {
            throw new RuntimeException(\sprintf('Could not find price field with xPath "%s"', $priceFieldXpath));
        }
        $priceField->click();

        $priceInputXpath = $builder->reset()->child('input', ['@name' => $inputName], 1)->getXpath();

        $this->waitForSelectorPresent('xpath', $priceInputXpath);
        $this->waitForSelectorVisible('xpath', $priceInputXpath);
        $priceInput = $this->find('xpath', $priceInputXpath);

        $priceInput->setValue($value);

        // Minks keyPress-method is buggy unfortunately, this is a workaround to de-focus the price input so its value is actually set.
        $window->click();
    }
}
