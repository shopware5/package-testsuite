<?php

declare(strict_types=1);

namespace Shopware\Element\Backend\Form;

use Shopware\Component\XpathBuilder\BackendXpathBuilder;
use Shopware\Element\Backend\ExtJsElement;

class Checkbox extends ExtJsElement
{
    public function isChecked(): bool
    {
        $checkedXpath = BackendXpathBuilder::create()
            ->ancestor('table', ['~class' => 'x-form-cb-checked'], 1)
            ->getXpath();

        $checked = $this->find('xpath', $checkedXpath);

        return $checked !== null;
    }

    public function toggle(): void
    {
        $this->click();
    }
}
