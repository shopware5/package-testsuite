<?php

declare(strict_types=1);

namespace Shopware\Context;

use RuntimeException;
use Shopware\Page\Backend\QuestionMarkModule;

class BackendQuestionMarkContext extends SubContext
{
    /**
     * @Then I should see a correct build number
     */
    public function iShouldSeeACorrectBuildNumber(): void
    {
        $buildNr = $this->getValidPage(QuestionMarkModule::class)->getBuildNr();
        if (!strtotime($buildNr)) {
            throw new RuntimeException(\sprintf('Build number "%s" is wrong', $buildNr));
        }
    }

    /**
     * @Then I should see a correct version number
     */
    public function iShouldSeeACorrectVersionNumber(): void
    {
        $versionNr = $this->getValidPage(QuestionMarkModule::class)->getVersionNr();
        if (!version_compare($versionNr, '0.0.1', '>')) {
            throw new RuntimeException(\sprintf('Version number "%s" is wrong', $versionNr));
        }
    }
}
