<?php

declare(strict_types=1);

namespace Shopware\Element\Backend;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use RuntimeException;

/**
 * Base class for all ExtJs element representations.
 *
 * Elements are identified by an xpath expression and the current session.
 * Both are required by Behat's NodeElement class. For simplicity, child
 * classes can implement a static createFrom... method that takes e.g.
 * an input label or a window name to abstract away any xpath manipulation.
 *
 * Classes extending this class are supposed to implement common ExtJs
 * behaviour in an object-oriented fashion, i.e. Checkbox::toggle() or
 * possibly things like Window::clickOnTab().
 */
abstract class ExtJsElement extends NodeElement
{
    /**
     * Behat expects an xpath to identify an element as well as the current session,
     * as node elements are accessed and manipulated lazily & in real time. Xpaths
     * should always start at the page root level, i.e. //* or BaseXpathBuilder::create();
     *
     * When dealing with nested elements, such as input fields within windows,
     * the class implementing the behavior should concatenate or scope the xpath for the nested
     * element to the context of the parent. Take a look at the Shopware\Element\Backend\Window class
     * for an example of this abstract xpath handling.
     *
     * @param string $xpath
     */
    public function __construct($xpath, Session $session)
    {
        parent::__construct($xpath, $session);
        $this->waitForElementAvailable();
    }

    /**
     * Wait for element to be accessible and visible
     *
     * A large part of Minks instability with ExtJS stems from timing issues
     * due to dynamic DOM manipulation and the like. Whenever an ExtJsElement
     * is created, this function checks if the element indeed exists and can
     * be used in testing, failing early with an exception if it doesn't.
     */
    protected function waitForElementAvailable(): void
    {
        // Check if the element already exists in the DOM
        if ($this->isValid()) {
            return;
        }

        // Otherwise, allow for element initialisation
        sleep(2);
        $this->waitFor(10, function (ExtJsElement $element) {
            return $element->isValid() && $element->isVisible();
        });

        // Check if object exists
        if (!$this->isValid()) {
            throw new RuntimeException('Could not find element of type ' . \get_class($this) . ' with xpath: ' . $this->getXpath());
        }

        // Check if object is visible
        if (!$this->isVisible()) {
            throw new RuntimeException('Element of type ' . \get_class($this) . ' not visible.');
        }
    }
}
