<?php

namespace Shopware\Element\Backend\Form;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

class Checkbox extends ExtJsElement
{
    /**
     * @return bool
     */
    public function isChecked()
    {
        $checkedXpath = BackendXpathBuilder::create()
            ->ancestor('table', ['~class' => 'x-form-cb-checked'], 1)
            ->getXpath();

        $checked = $this->find('xpath', $checkedXpath);

        return null !== $checked;
    }

    public function toggle()
    {
        $this->click();
    }
}