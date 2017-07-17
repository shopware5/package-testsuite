<?php

namespace Shopware\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Component\Helper\HelperSelectorInterface;

class Backend extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/backend/';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * Click on a tab identified by its label
     *
     * @param string $label
     */
    public function clickOnTabWithName($label)
    {
        $tabXpath = BackendXpathBuilder::create()
            ->child('span', ['@text' => $label, 'and', '~class' => 'x-tab-inner'])
            ->ancestor('button', [], 1)
            ->getXpath();

        $element = $this->find('xpath', $tabXpath);
        $element->click();
    }

}
