<?php

namespace Shopware\Element\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

/**
 * Class Window
 *
 * Representing an ExtJS window, identified by its title.
 */
class Window extends NodeElement
{
    /**
     * ExtJS Window Title
     *
     * @var string
     */
    private $title;

    /**
     * Window constructor.
     * @param string $title
     * @param Session $session
     * @param bool $exactTitleMatch
     * @throws \Exception
     */
    public function __construct($title, Session $session, $exactTitleMatch = true)
    {
        $this->title = $title;

        $windowXpath = BackendXpathBuilder::getWindowXpathByTitle($title, $exactTitleMatch);
        parent::__construct($windowXpath, $session);

        $this->waitForWindowVisible();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Close the ExtJs window
     */
    public function close()
    {
        $closeButton = $this->find('xpath', $this->getWindowCloseButtonXpath());
        $closeButton->click();
    }

    /**
     * Get a grid view enclosed in the current window
     *
     * @return GridView
     * @throws \Exception
     */
    public function getGridView()
    {
        $gridView = new GridView($this->getGridViewXpath(), $this->getSession());

        return $gridView;
    }

    /**
     * Wait for the extJS window to be valid and visible
     */
    private function waitForWindowVisible()
    {
        sleep(2);

        $this->waitFor(10, function (Window $window) {
            return $window->isValid() && $window->isVisible();
        });

        if (!$this->isValid()) {
            throw new \Exception('Could not find window with title: ' . $this->getTitle());
        }

        if (!$this->isVisible()) {
            throw new \Exception('Window with title ' . $this->getTitle() . 'not visible.');
        }
    }

    /**
     * @return string
     */
    private function getWindowCloseButtonXpath()
    {
        $closeButtonXpath = BackendXpathBuilder::create($this->getXpath())
            ->descendant('div', ['~class' => 'x-window-header'])
            ->descendant('img', ['~class' => 'x-tool-close'])
            ->getXpath();
        return $closeButtonXpath;
    }

    /**
     * @return string
     */
    private function getGridViewXpath()
    {
        $gridViewXpath = BackendXpathBuilder::create($this->getXpath())
            ->descendant('div', ['~class' => 'x-grid-with-row-lines'])
            ->getXpath();
        return $gridViewXpath;
    }
}