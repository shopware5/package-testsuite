<?php

namespace Shopware\Tests\Mink\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Backend extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/backend/';

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
