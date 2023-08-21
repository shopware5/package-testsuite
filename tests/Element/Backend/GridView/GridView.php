<?php

declare(strict_types=1);

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
    public function reload(): void
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
     * @return array<GridViewRow>
     */
    public function getRows(): array
    {
        $rows = $this->findAll('xpath', $this->getGridViewRowsXpath());

        return array_map(function (NodeElement $element) {
            return new GridViewRow($element->getXpath(), $this->getSession());
        }, $rows);
    }

    /**
     * Returns the first row from a grid view
     */
    public function getFirstRow(): GridViewRow
    {
        $xPath = $this->getGridViewFirstRowXpath();
        $element = $this->find('xpath', $xPath);
        if (!$element instanceof NodeElement) {
            throw new \RuntimeException(sprintf('Could not find grid view row with xPath: "%s"', $xPath));
        }

        return new GridViewRow($element->getXpath(), $this->getSession());
    }

    /**
     * Get the first row that contains given string
     */
    public function getRowByContent(string $content): GridViewRow
    {
        foreach ($this->getRows() as $row) {
            if (strpos($row->getHtml(), $content)) {
                return $row;
            }
        }

        throw new \RuntimeException('Could not find grid view row by with content: ' . $content);
    }

    /**
     * Sort a grid view by a given header name
     */
    public function sortBy(string $headerName): void
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

    private function getReloadButtonXpath(): string
    {
        return BackendXpathBuilder::create()
            ->descendant('span', ['~class' => 'x-tbar-loading'])
            ->getXpath();
    }

    private function getGridViewRowsXpath(): string
    {
        return BackendXpathBuilder::create()
            ->descendant('tr', ['~class' => 'x-grid-row'])
            ->getXpath();
    }

    private function getGridViewFirstRowXpath(): string
    {
        return sprintf('(%s)[1]', $this->getGridViewRowsXpath());
    }
}
