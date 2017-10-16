<?php

namespace Shopware\Page\Backend;


use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class ExistingArticleModule extends NewArticleModule
{
    /**
     * Changes the name of the selected article
     *
     * @param $newName
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
     * @param $groupname
     * @param $label
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
}