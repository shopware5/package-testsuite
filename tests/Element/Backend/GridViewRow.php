<?php

namespace Shopware\Element\Backend;

use Behat\Mink\Element\NodeElement;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class GridViewRow extends NodeElement
{
    /**
     * Click an icon in a grid view row.
     * This could e.g. be an edit icon for a customer ('sprite-pencil') or some other extJS icon.
     *
     * @param string $iconClass
     */
    public function clickActionIcon($iconClass)
    {
        $iconXpath = BackendXpathBuilder::create()->child('img', ['~class' => $iconClass])->getXpath();
        $icon = $this->find('xpath', $iconXpath);
        $icon->click();
    }
}