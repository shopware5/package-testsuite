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
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle($this->windowTitle)->get();

        $window = $this->find('xpath', $windowXpath);
        $this->assertNotNull($window, print_r('Could not find element with XPath ' . $windowXpath, true));

        $gridXPath = $xp
            ->div('desc', ['~text' => $item])
            ->table('asc', [], 1)
            ->get();
        $this->waitForXpathElementPresent($gridXPath);

        $grid = $window->find('xpath', $gridXPath);
        $this->assertNotNull($grid, print_r('Could not find element with XPath ' . $gridXPath, true));

        if ($meetRequirements) {
            $this->checkStatusGreen($grid, $xp, $item);
            return;
        }
        $this->checkStatusRed($grid, $xp, $item);
    }

    /**
     * Checks, if the status of the given entry is crossed
     *
     * @param NodeElement $grid Area in which the entry is located as a row
     * @param XpathBuilder $xp
     * @param string $label Label of the requirement
     *
     */
    private function checkStatusRed($grid, $xp, $label)
    {
        $statusXpath = $xp->div('desc', ['@text' => $label])->tr('asc', [], 1)->get();
        $statusCell = $grid->find('xpath', $statusXpath);
        $this->assertNotNull($statusCell, print_r('Could not find element with XPath cell ' . $statusXpath, true));

        $statusRedXpath = $xp->div('desc', ['~class' => 'sprite-cross'])->get();
        $statusRed = $statusCell->find('xpath', $statusRedXpath);
        $this->assertNotNull($statusRed, print_r('Could not find element with XPath status ' . $statusRedXpath, true));
    }

    /**
     * Checks, if the status of the given entry is ticked
     *
     * @param NodeElement $grid Area in which the entry is located as a row
     * @param XpathBuilder $xp
     * @param string $label Label of the requirement
     */
    private function checkStatusGreen($grid, $xp, $label)
    {
        $statusXpath = $xp->div('desc', ['@text' => $label])->tr('asc', [], 1)->get();
        $statusCell = $grid->find('xpath', $statusXpath);
        $this->assertNotNull($statusCell, print_r('Could not find element with XPath cell ' . $statusXpath, true));

        $statusRedXpath = $xp->div('desc', ['~class' => 'sprite-tick'])->get();
        $statusRed = $statusCell->find('xpath', $statusRedXpath);
        $this->assertNotNull($statusRed, print_r('Could not find element with XPath status ' . $statusRedXpath, true));
    }
}
