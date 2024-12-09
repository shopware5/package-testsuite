<?php

declare(strict_types=1);

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class SystemInfoModule extends BackendModule
{
    protected string $moduleWindowTitle = 'System-Informationen';

    /**
     * Checks if all requirements are fulfilled
     *
     * @param string $item           The requirement which should be checked
     * @param bool   $expectedStatus Determines if the requirement should be met or not
     **/
    public function checkRequirements(string $item, bool $expectedStatus): void
    {
        if ($expectedStatus) {
            $this->checkStatus($item, 'sprite-tick');
        } else {
            $this->checkStatus($item, 'sprite-cross');
        }
    }

    /**
     * Check if a given requirement has a given state
     */
    private function checkStatus(string $item, string $class): void
    {
        $grid = $this->getGridForGridItem($item);
        $statusXpath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $item])
            ->ancestor('tr', [], 1)
            ->descendant('div', ['~class' => $class])
            ->getXpath();

        $grid->find('xpath', $statusXpath);
    }

    /**
     * Get the requirements grid for a given grid item
     */
    private function getGridForGridItem(string $item): NodeElement
    {
        $window = $this->getModuleWindow();
        $gridXPath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $item])
            ->ancestor('table', [], 1)
            ->getXpath();

        return $window->find('xpath', $gridXPath);
    }
}
