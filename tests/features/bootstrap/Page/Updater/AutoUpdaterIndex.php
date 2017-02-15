<?php

namespace Shopware\Tests\Mink\Page\Updater;

use Behat\Mink\Element\NodeElement;
use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class AutoUpdaterIndex extends ContextAwarePage implements HelperSelectorInterface
{
    /**
     * * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        $xp = new XpathBuilder();
        return [
            'shopBackend' => $xp->a(['~class' => 'is--right', 'and', '@text' => 'Zum Shop-Backend (Administration)'])->get(),
            'shopFrontend' => $xp->a(['~class' => 'is--left'])->get(),
        ];
    }

    /**
     * * {@inheritdoc}
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
     * Clicks a specific element
     *
     * @param string $elementName Name of the element
     **/

    public function clickOnElement($elementName)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $elementXpath = $xp->span(['@text' => $elementName, 'and', '~class' => 'x-btn-inner'])->button('asc', [], 1)->get();

        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }

    /**
     * Clicks a specific tab
     *
     * @param string $tabName Name of the tab
     **/

    public function clickOnTab($tabName)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $elementXpath = $xp->span(['@text' => $tabName, 'and', '~class' => 'x-tab-inner'])->button('asc', [], 1)->get();

        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }

    /**
     * Clicks the button to refresh the module
     *
     **/

    public function refreshElement()
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle('Softwareaktualisierung')->get();
        $window = $this->find('xpath', $windowXpath);

        $elementXpath = $xp->button(['@data-qtip' => 'Aktualisieren'])->get();

        $element = $window->find('xpath', $elementXpath);
        $element->click();
    }

    /**
     * Checks if an element is diabled or not
     *
     * @param string $selector The selector of the element
     * @param string $locator The place the element can be found
     * @param bool $desiredMode Determines if the element should be disabled or enabled
     * @throws \Exception
     **/

    public function checkIfEnabled($selector, $locator, $desiredMode)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $elementXpath = $xp->span(['@text' => $locator, 'and', '~class' => 'x-btn-inner'])->button('asc', [], 1)->get();
        $element = $this->find($selector, $elementXpath);
        $element->hasAttribute('disabled');

        if ($desiredMode === false) {
            if (!$element->hasAttribute('disabled')) {
                throw new \Exception('Element should be disbled in this case, but is not.');
            }
        } else {
            if ($element->hasAttribute('disabled')) {
                throw new \Exception('Element should be disbled in this case, but is not.');
            }
        }
    }

    /**
     * Checks if all requirements are fullfilled
     *
     * @param string $item The requirement which should be checked
     * @throws \Exception
     **/
    public function checkSystemRequirements($item)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle('Softwareaktualisierung')->get();
        $window = $this->find('xpath', $windowXpath);

        $gridXPath = $xp
            ->div('desc', ['~text' => $item])
            ->table('asc', [], 1)
            ->get()
        ;

        $this->waitForXpathElementPresent($gridXPath);

        $grid = $window->find('xpath', $gridXPath);
        $rowsXpath = $xp->tr('desc', [])->get();

        /** @var NodeElement[] $rows */
        $rows = $grid->findAll('xpath', $rowsXpath);

        foreach ($rows as $row) {
            $firstChild = $row->find('xpath', '/*[1]');
            if ($firstChild->getTagName() !== 'td') {
                continue;
            }

            $statusXpath = $xp->td('desc', [])->get().'[position()=1]';
            $messageXpath = $xp->td('desc', [])->get().'[position()=2]';

            $message = $row->find('xpath', $messageXpath)->getText();
            $statusCell = $row->find('xpath', $statusXpath);
            $statusGreen = $statusCell->find('xpath', $xp->img('desc', ['~class' => 'sprite-tick'])->get());

            if ($statusGreen === null) {
                throw new \Exception('Requirement not met: "'.$message.'"');
            }
        }
    }

    /**
     * Checks if some of the requirements are unfullfilled
     *
     * @param string $tabName defines the name of the tab
     * @return bool
     */
    public function checkSystemRequirementsUnfullfilled($tabName)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle('Softwareaktualisierung')->get();
        $window = $this->find('xpath', $windowXpath);

        $tabXPath = $xp->span('desc', ['@text' => $tabName, 'and', '~class' => 'x-tab-inner'])->button('asc', [], 1)->span('desc', ['~class' => 'sprite-cross'])->get();
        $this->waitForXpathElementPresent($tabXPath);

        $tab = $window->find('xpath', $tabXPath);

        if (!$tab instanceof NodeElement) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the tab signalizes fullfilled requirements
     *
     * @param string $tab defines the name of the tab
     * @return bool
     **/
    public function checkTabRequirementFullfilled($tab)
    {
        /** @var XpathBuilder $xp */
        $xp = new XpathBuilder();

        $windowXpath = $xp->xWindowByExactTitle('Softwareaktualisierung')->get();
        $window = $this->find('xpath', $windowXpath);

        $tabXPath = $xp->span('desc', ['@text' => $tab, 'and', '~class' => 'x-tab-inner'])->button('asc', [], 1)->span('desc', ['~class' => 'sprite-cross'])->get();
        $this->waitForXpathElementPresent($tabXPath);

        $tab = $window->find('xpath', $tabXPath);

        if (!$tab instanceof NodeElement) {
            return false;
        }
        return true;
    }

    /**
     * Ticks the checkbox to confirm that a backup was really created
     *
     **/
    public function tickConfirmationCheckbox()
    {
        $xp = new XpathBuilder();

        $elementXpath = $xp->label(['@text' => 'Ich habe ein Backup angelegt und möchte das Update durchführen.', 'and', '~class' => 'x-form-cb-label'])->td('asc', [], 1)->get();

        /** @var NodeElement $element */
        $element = $this->find('xpath', $elementXpath);
        $element->click();
    }


    /**
     * Checks if the new shop is available after installation
     *
     * @param string $type Frontend or Backend of the shop
     * @param string $target Actual target of the link
     * @throws \Exception
     **/
    public function checkIfShopIsAvailable($type, $target)
    {
        $xpath = $this->getXPathSelectors();
        /** @var NodeElement $shopLink */
        $shopLink = $this->find('xpath', $xpath[$type]);

        if ($shopLink->hasLink($target) === null) {
            throw new \Exception('There is no button leading to ' . $type);
        }
    }

    /**
     * Clicks a chosen element in the updater process itself
     *
     * @param string $elementName Name of the element
     *
     **/
    public function clickOnUpdaterElement($elementName)
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->find('xpath', $xpath[$elementName]);
        $forwardButton->click();
    }
}
