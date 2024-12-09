<?php

declare(strict_types=1);

namespace Shopware\Page\Updater;

use Shopware\Component\XpathBuilder\BaseXpathBuilder;
use Shopware\Page\ContextAwarePage;

class UpdaterIndex extends ContextAwarePage
{
    /**
     * @var string
     */
    protected $path = '/recovery/update/';

    public function getXPathSelectors(): array
    {
        return [
            'forwardButton' => BaseXpathBuilder::create()->child('input', ['@value' => 'Weiter'])->getXpath(),
            'requirementForwardButton' => BaseXpathBuilder::create()
                ->child('button', ['@type' => 'submit'])
                ->getXpath(),
            'cleanupStatusElement' => BaseXpathBuilder::create()
                ->child('div', ['@class' => 'fileCounterContainer', 'and', '~class' => 'is--left'])
                ->getXpath(),
        ];
    }

    public function getCssSelectors(): array
    {
        return [
            'startDbMigrationButton' => '#start-ajax',
        ];
    }

    /**
     * Advances to the next updater page
     */
    public function advance(): void
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->waitForSelectorPresent('xpath', $xpath['forwardButton']);

        $forwardButton->click();
    }

    /**
     * Starts the database migration
     */
    public function clickOnDbStart(): void
    {
        $forwardButton = $this->find('css', $this->getCssSelectors()['startDbMigrationButton']);
        $forwardButton->click();
    }

    /**
     * Indicates the finished Cleanup step
     */
    public function finishCleanup(): void
    {
        $this->waitForTextNotPresent('entfernte Dateien');
        $this->waitForSelectorNotPresent('css', '.loading-indicator');
    }

    /**
     * Provides the handling of the update-asset folder after the update is finshed
     *
     * @param string $updateTitle Text which indicates the hint to remove the update assets
     */
    public function handleUpdateAssets(string $updateTitle): void
    {
        $this->waitForText($updateTitle);
    }

    /**
     * Advances to the next updater page
     *
     * @param string $stepName Name of the step from which the navigation will proceed
     */
    public function advanceToStep(string $stepName): void
    {
        $xpath = $this->getXPathSelectors();
        $forwardButton = $this->waitForSelectorPresent('xpath', $xpath[$stepName]);

        $forwardButton->click();
    }
}
