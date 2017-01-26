<?php

namespace Shopware\Tests\Mink\Page\Updater;

use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class SystemRequirements extends ContextAwarePage implements HelperSelectorInterface
{

    /**
     * @var string $path
     */
    protected $path = '/recovery/update/checks';

    /**
     * * {@inheritdoc}
     */
    public function getXPathSelectors()
    {
        $xp = new XpathBuilder();
        return [
            'forwardButton' => $xp->button(['@type' => 'submit', 'and', '~class' => 'btn-arrow-right'])->get(),
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

    public function advance()
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->find('xpath', $xpath['forwardButton']);
        $forwardButton->click();
    }
}
