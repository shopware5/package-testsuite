<?php

declare(strict_types=1);

namespace Shopware\Context;

use Shopware\Page\Backend\QuestionMarkModule;

class BackendQuestionMarkContext extends SubContext
{
    /**
     * @Then I should see a correct build number
     */
    public function iShouldSeeACorrectBuildNumber(): void
    {
        $questionMark = $this->getValidPage(QuestionMarkModule::class);
        $buildNr = $questionMark->getBuildNr();
        if (!strtotime($buildNr)) {
            throw new \Exception('Build number wrong');
        }
    }

    /**
     * @Then I should see a correct version number
     */
    public function iShouldSeeACorrectVersionNumber(): void
    {
        $questionMark = $this->getValidPage(QuestionMarkModule::class);
        $versionNr = $questionMark->getVersionNr();
        if (!version_compare($versionNr, '0.0.1', '>')) {
            throw new \Exception('Version number wrong!');
        }
    }
}
