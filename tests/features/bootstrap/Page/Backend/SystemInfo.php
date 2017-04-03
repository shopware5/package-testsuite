<?php

namespace Shopware\Tests\Mink\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class SystemInfo extends ContextAwarePage implements HelperSelectorInterface
{
    private $windowTitle = "System-Informationen";

    /**
     * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Checks if all requirements are fulfilled
     *
     * @param string $item The requirement which should be checked
     * @param bool $meetRequirements Determines if the requirement should be met or not
     **/
    public function checkRequirements($item, $meetRequirements)
    {
        if ($meetRequirements) {
            $this->checkStatus($item, 'sprite-tick');
            return;
        }
        $this->checkStatus($item, 'sprite-cross');
    }

    /**
     * @param string $item
     * @param string $class
     */
    private function checkStatus($item, $class)
    {
        $xp = new XpathBuilder();
        $grid = $this->getSystemGrid($item);

        $statusXpath = $xp->div('desc', ['@text' => $item])->tr('asc', [], 1)->get();
        $statusCell = $grid->find('xpath', $statusXpath);
        $this->assertNotNull($statusCell, sprintf('Could not find element with XPath cell %s', $statusXpath));

        $statusXpath = $xp->div('desc', ['~class' => $class])->get();
        $status = $statusCell->find('xpath', $statusXpath);
        $this->assertNotNull($status, sprintf('Could not find element with XPath status %s', $statusXpath));
    }

    /**
     * @param string $item
     * @return NodeElement|null
     */
    private function getSystemGrid($item)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle($this->windowTitle)->get();

        $window = $this->find('xpath', $windowXpath);
        $this->assertNotNull($window, sprintf('Could not find element with XPath %s', $windowXpath));

        $gridXPath = $xp->div('desc', ['~text' => $item])->table('asc', [], 1)->get();
        $this->waitForXpathElementPresent($gridXPath);

        $grid = $window->find('xpath', $gridXPath);
        $this->assertNotNull($grid, sprintf('Could not find element with XPath %s', $gridXPath));

        return $grid;
    }
}
