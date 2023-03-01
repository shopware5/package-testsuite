<?php

declare(strict_types=1);

namespace Shopware\Element\Backend\GridView;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

class GridViewRow extends ExtJsElement
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
