<?php

namespace Shopware\Tests\Mink\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Payment extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/backend/?app=Payment';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getXPathSelectors()
    {
        return [];
    }
}
