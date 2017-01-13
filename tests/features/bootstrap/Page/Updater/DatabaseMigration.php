<?php

namespace Shopware\Tests\Mink\Page\Updater;

use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class DatabaseMigration extends ContextAwarePage implements HelperSelectorInterface
{

    /**
     * @var string $path
     */
    protected $path = '/recovery/update/';

    /**
     * * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        $xp = new XpathBuilder();
        return [
            'forwardButton' => $xp->input(['@type' => 'submit', 'and', '@value' => 'Weiter'])->get(),
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
}