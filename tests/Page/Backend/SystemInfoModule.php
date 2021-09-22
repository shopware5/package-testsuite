<?php

namespace Shopware\Page\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class SystemInfoModule extends BackendModule
{
    /**
     * @var string
     */
    protected $moduleWindowTitle = 'System-Informationen';

    /**
     * Checks if all requirements are fulfilled
     *
     * @param string $item           The requirement which should be checked
     * @param bool   $expectedStatus Determines if the requirement should be met or not
     **/
    public function checkRequirements($item, $expectedStatus)
    {
        if ($expectedStatus) {
            $this->checkStatus($item, 'sprite-tick');
        } else {
            $this->checkStatus($item, 'sprite-cross');
        }
    }

    /**
     * Check if a given requirement has a given state
     *
     * @param string $item
     * @param string $class
     *
     * @return bool
     */
    private function checkStatus($item, $class)
    {
        $grid = $this->getGridForGridItem($item);
        $statusXpath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $item])
            ->ancestor('tr', [], 1)
            ->descendant('div', ['~class' => $class])
            ->getXpath();

        return $grid->find('xpath', $statusXpath) !== null;
    }

    /**
     * Get the requirements grid for a given grid item
     *
     * @param string $item
     *
     * @return NodeElement|null
     */
    private function getGridForGridItem($item)
    {
        $window = $this->getModuleWindow();
        $gridXPath = BackendXpathBuilder::create()
            ->child('div', ['@text' => $item])
            ->ancestor('table', [], 1)
            ->getXpath();

        return $window->find('xpath', $gridXPath);
    }
}
