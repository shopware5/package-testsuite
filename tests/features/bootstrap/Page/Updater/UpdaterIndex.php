<?php

namespace Shopware\Tests\Mink\Page\Updater;

use Shopware\Helper\ContextAwarePage;
use Shopware\Helper\XpathBuilder;
use Shopware\Tests\Mink\HelperSelectorInterface;

class UpdaterIndex extends ContextAwarePage implements HelperSelectorInterface
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
            'forwardButton' => $xp->input(['@value' => 'Weiter'])->get(),
            'requirementForwardButton' => $xp->button(['@type' => 'submit'])->get(),
            'cleanupStatusElement' => $xp->div(['@class' => 'fileCounterContainer', 'and', '~class' => 'is--left'])->get(),
        ];
    }

    /**
     * * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'startDbMigrationButton' => '#start-ajax',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Advances to the next updater page
     *
     */
    public function advance()
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->waitForSelectorPresent('xpath', $xpath['forwardButton']);

        $forwardButton->click();
    }

    /**
     * Starts the database migration
     *
     *
     *
     */
    public function clickOnDbStart($text)
    {
        $cssSelectors = $this->getCssSelectors();
        $forwardButton = $this->find('css', $cssSelectors[$text]);
        $forwardButton->click();
    }

    /**
     * Indicates the finished Cleanup step
     *
     */
    public function finishCleanup()
    {
        $textNotPresent = $this->waitForTextNotPresent("entfernte Dateien");
        $indicatorNotPresent = $this->waitForSelectorNotPresent('css', '.loading-indicator');

        if($textNotPresent === false || $indicatorNotPresent === false ){
            throw new \Exception('Cleanup could not be finished');
        }
    }

    /**
     * Provides the handling of the update-asset folder after the update is finshed
     *
     */
    public function handleUpdateAssets()
    {
        $this->waitForText("Ihr Shop befindet sich zurzeit im Wartungsmodus.");
    }

    /**
     * Advances to the next updater page
     *
     */
    public function advanceToStep($stepName)
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->waitForSelectorPresent('xpath', $xpath[$stepName]);

        $forwardButton->click();
    }
}