<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Backend\SystemInfoModule;

class FileCheckContext extends SubContext
{
    private $testPath;
    private $folderRequirementLabel = 'media/music/';
    private $fileRequirementLabel = 'engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap.php';
    private $renamedFileRequirementLabel = '/engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap-.php';
    private $unfulfilledIcon = 'cross';

    public function __construct()
    {
        parent::__construct();
        $this->testPath = getenv('base_path');
    }

    /**
     * @Given the filecheck requirements are not met
     */
    public function theFilecheckRequirementsAreNotMet()
    {
        $this->setRequirementsFulfillment(false);
    }


    /**
     * @When I correct the :requirement requirement
     */
    public function iCorrectTheRequirement($requirement)
    {
        $this->setRequirementsFulfillment(true, $requirement);
    }

    /**
     * @Then a :requirement requirement should have a :icon as status
     * @Then all :requirement requirements should have a :icon as status
     */
    public function aRequirementShouldOwnAsStatus($requirement, $icon)
    {
        /** @var SystemInfoModule $page */
        $page = $this->getPage('SystemInfoModule');
        $requirementLabel = $requirement === 'folder' ? $this->folderRequirementLabel : $this->fileRequirementLabel;

        $page->checkRequirements(
            $requirementLabel,
            $icon !== $this->unfulfilledIcon
        );
    }


    /**
     * Fakes an unfulfilled requirement or undoes it
     * @param bool $meetRequirements Determines if the requirement should be met or not
     * @param string $type Determines if the requirement should be defined fo a specific element (optional)
     */
    private function setRequirementsFulfillment($meetRequirements, $type = '')
    {
        if ($meetRequirements === false) {
            rename($this->testPath . '/' . $this->fileRequirementLabel, $this->testPath . $this->renamedFileRequirementLabel);
            chmod($this->testPath . '/' . $this->folderRequirementLabel, 0444);
            return;
        }

        switch ($type) {
            case 'file':
                rename($this->testPath . $this->renamedFileRequirementLabel, $this->testPath . '/' . $this->fileRequirementLabel);
                break;
            case 'folder':
                chmod($this->testPath . '/' . $this->folderRequirementLabel, 0777);
                break;
        }
    }
}
