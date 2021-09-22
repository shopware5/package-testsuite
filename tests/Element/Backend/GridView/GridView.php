<?php

namespace Shopware\Element\Backend\GridView;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

/**
 * Represents an ExtJS grid view
 */
class GridView extends ExtJsElement
{
    /**
     * Reload the content of the grid view
     */
    public function reload()
    {
        $reloadButton = $this->find('xpath', $this->getReloadButtonXpath());

        // Wait to click button
        $this->waitFor(5, function () use ($reloadButton) {
            return $reloadButton->isVisible();
        });

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
     * Returns the first row from a grid view
     *
     * @return GridViewRow
     */
    public function getFirstRow()
    {
        return $this->find('xpath', $this->getGridViewFirstRowXpath());
    }

    /**
     * Get the first row that contains given string
     *
     * @param string $content
     *
     * @throws \Exception
     *
     * @return GridViewRow
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
     * Sort a grid view by a given header name
     *
     * @param string $headerName
     */
    public function sortBy($headerName)
    {
        $tableHeaderXpath = BackendXpathBuilder::create()
            ->child('span', ['~class' => 'x-column-header-text'])
            ->contains($headerName)
            ->getXpath();

        $tableHeader = $this->find('xpath', $tableHeaderXpath);

        $this->waitFor(5, function () use ($tableHeader) {
            return $tableHeader->isVisible();
        });

        $tableHeader->click();
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

    private function getGridViewFirstRowXpath(): string
    {
        return sprintf('(%s)[1]', $this->getGridViewRowsXpath());
    }
}
