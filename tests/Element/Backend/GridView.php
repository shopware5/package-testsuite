<?php

namespace Shopware\Element\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class GridView extends NodeElement
{
    /**
     * @inheritdoc
     */
    public function __construct($xpath, Session $session)
    {
        parent::__construct($xpath, $session);

        $this->waitForGridViewVisible($xpath);
    }

    /**
     * Reload the content of the grid view
     */
    public function reload()
    {
        $reloadButton = $this->find('xpath', $this->getReloadButtonXpath());
        $reloadButton->click();
    }

    /**
     * Return all visible rows from a grid view
     *
     * @return GridViewRow[]
     */
    public function getRows()
    {
        $rows = $this->findAll('xpath', $this->getGridViewRowsXpath());

        return array_map(function (NodeElement $element) {
            return new GridViewRow($element->getXpath(), $this->getSession());
        }, $rows);
    }

    /**
     * Get the first row that contains given string
     *
     * @param string $content
     * @return GridViewRow
     * @throws \Exception
     */
    public function getRowByContent($content)
    {
        foreach ($this->getRows() as $row) {
            if (strpos($row->getHtml(), $content)) {
                return $row;
            }
        }

        throw new \Exception('Could not find grid view row by with content: ' . $content);
    }

    /**
     * @return string
     */
    private function getReloadButtonXpath()
    {
        $reloadButtonXpath = BackendXpathBuilder::create()
            ->descendant('span', ['~class' => 'x-tbar-loading'])
            ->getXpath();
        return $reloadButtonXpath;
    }

    /**
     * @return string
     */
    private function getGridViewRowsXpath()
    {
        $rowsXpath = BackendXpathBuilder::create()
            ->descendant('tr', ['~class' => 'x-grid-row'])
            ->getXpath();
        return $rowsXpath;
    }

    /**
     * @param $xpath
     * @throws \Exception
     */
    private function waitForGridViewVisible($xpath)
    {
        if (!$this->isValid()) {
            throw new \Exception('Could not find grid view using xpath ' . $xpath);
        }
    }
}