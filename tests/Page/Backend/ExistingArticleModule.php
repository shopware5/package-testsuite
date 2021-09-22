<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class ExistingArticleModule extends NewArticleModule
{
    /**
     * Changes the name of the selected article
     *
     * @param string $newName
     */
    public function changeArticleName($newName)
    {
        $input = $this->find('xpath', BackendXpathBuilder::getInputXpathByLabel('Artikel-Bezeichnung:'));
        $input->click();
        $input->setValue($newName);
    }

    /**
     * Creates the group of a configurator set
     *
     * @param string $groupname
     * @param string $label
     */
    public function createVariantGroup($groupname, $label)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $inputXpath = $builder
            ->child('label', ['@text' => $label])
            ->ancestor('tr', [], 1)
            ->descendant('input', ['~class' => 'x-form-field'], 1)
            ->getXpath();

        $input = $window->find('xpath', $inputXpath);
        $input->setValue($groupname);
    }

    /**
     * Checks if the group is located in the desired area, e.g. under "active"
     *
     * @param string $title
     * @param string $grouptitle
     * @return bool
     */
    public function checkIfMatchesTheRightGroup($title, $grouptitle)
    {
        $builder = new BackendXpathBuilder();

        $groupMatchXpath = $builder
            ->child('div', ['@text' => $title])
            ->ancestor('tbody', [], 1)
            ->descendant('span', ['@text' => $grouptitle], 1)
            ->getXpath();

        return $groupMatchXpath !== null;
    }

    /**
     * Clicks the group of the configurator set in order to edit it
     *
     * @param string $groupname
     */
    public function clickToEditGroup($groupname)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $inputXpath = $builder
            ->child('div', ['@text' => $groupname])
            ->ancestor('tr', ['~class' => 'x-grid-row'], 1)
            ->getXpath();

        $groupEntry = $window->find('xpath', $inputXpath);
        $groupEntry->click();
    }


    /**
     * Creates the options of a configurator set
     *
     * @param array $data
     * @param string $label
     */
    public function createOptionsForGroup($data, $label)
    {
        $window = $this->getModuleWindow(false);
        $builder = new BackendXpathBuilder();

        $inputXpath = $builder
            ->child('label', ['@text' => $label])
            ->ancestor('tr', [], 1)
            ->descendant('input', ['~class' => 'x-form-field'], 1)
            ->getXpath();

        $activeButtonXpath = $builder
            ->reset()
            ->child('label', ['@text' => $label])
            ->ancestor('div', ['~class' => 'x-toolbar'], 1)
            ->descendant('span', ['@text' => 'Erstellen und Aktivieren'], 1)
            ->getXpath();

        foreach ($data as $entry) {
            $groupEntry = $window->find('xpath', $inputXpath);
            $groupEntry->setValue($entry['option']);

            $button = $window->find('xpath', $activeButtonXpath);
            $button->click();
        }
    }

    /**
     * Fills in the property data of an article, excluding the values
     *
     * @param array $data
     * @throws \Exception
     */
    public function selectProperty($data)
    {
        $window = $this->getModuleWindow(false);

        // Fills most form elements
        $this->fillExtJsForm($window, $data);

        foreach ($data as $entry) {
            if ($entry['type'] === 'withoutlabel') {
                $this->chooseOption($entry['value']);
            }
        }
    }

    /**
     * Assigns an option to a selected group
     *
     * @param string $value
     * @throws \Exception
     */
    private function chooseOption($value)
    {
        $builder = new BackendXpathBuilder();
        $comboBoxXpath = $builder
            ->child('table', ['@data-action' => 'values-table'])
            ->descendant('td', [], 1)
            ->followingSibling('td', [], 1)
            ->descendant('div', ['~class' => 'x-form-trigger'])
            ->getXpath();

        $pebble = $this->find('xpath', $comboBoxXpath);
        $pebble->click();
        sleep(1);

        $options = $this->findAll('xpath',
            $builder->reset()->child('li', ['~class' => 'x-boundlist-item', 'and', '@text' => $value])->getXpath());

        /** @var NodeElement $option */
        foreach ($options as $option) {
            try {
                $option->click();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Checks if a specific value matches to a given property correctly
     *
     * @param string $group
     * @param string $value
     * @throws \Exception
     */
    public function checkCorrespondingPropertyValues($group, $value)
    {
        $builder = new BackendXpathBuilder();

        $matchXpath = $builder
            ->child('span', ['@text' => $group])
            ->ancestor('tr', [], 1)
            ->descendant('div', ['@text' => $value])
            ->getXpath();

        $this->waitForSelectorPresent('xpath', $matchXpath);
    }

    public function doInlineEditingOfVariant(string $orderNumber, string $additionalText): void
    {
        $builder = new BackendXpathBuilder();

        $orderNumberCell = $builder
            ->child('div', ['@text' => $orderNumber])
            ->ancestor('td')
            ->getXpath();

        $this->find('xpath', $orderNumberCell)->doubleClick();

        $builder->reset();

        $orderNumberInputFieldXPath = $builder->child('input', ['@name' => 'details.number'])->getXpath();

        $this->waitForSelectorPresent('xpath', $orderNumberInputFieldXPath);
        $this->waitForSelectorVisible('xpath', $orderNumberInputFieldXPath);

        $orderNumberInputField = $this->find('xpath', $orderNumberInputFieldXPath);
        $value = $orderNumberInputField->getValue();
        $orderNumberInputField->setValue($value . $additionalText);

        $orderNumberInputField->blur();
    }

    public function openVariantDetailPage(string $orderNumber): void
    {
        $variantRow = $this->getModuleWindow(false)->getGridView($orderNumber)->getRowByContent($orderNumber);
        $variantRow->clickActionIcon('sprite-pencil');
    }
}
