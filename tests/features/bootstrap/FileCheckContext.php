<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Backend\SystemInfo;

class FileCheckContext extends SubContext
{
    private $testPath;
    private $folderRequirementLabel = 'media/music/';
    private $fileRequirementLabel = 'engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap.php';
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
     * @Then a :requirement requirement should has a :icon as status
     * @Then all :requirement requirements should have a :icon as status
     */
    public function aRequirementShouldOwnAsStatus($requirement, $icon)
    {
        /** @var SystemInfo $page */
        $page = $this->getPage('SystemInfo');
        $requirementLabel = '';

        switch ($requirement) {
            case 'folder':
                $requirementLabel = $this->folderRequirementLabel;
                break;
            case 'file':
                $requirementLabel = $this->fileRequirementLabel;
                break;
        }

        if ($icon === $this->unfulfilledIcon) {
            $page->checkRequirements($requirementLabel, false);
        } else {
            $page->checkRequirements($requirementLabel, true);
        }
    }


    /**
     * Fakes an unfulfilled requirement or undoes it
     * @param bool $meetRequirements Determines if the requirement should be met or not
     * @param string $type Determines if the requirement should be defined fo a specific element (optional)
     */
    private function setRequirementsFulfillment($meetRequirements, $type = '')
    {
        if ($meetRequirements === false) {
            rename($this->testPath . '/engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap.php', $this->testPath . '/engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap-.php');
            chmod($this->testPath . '/media/music', 0444);
            return;
        }

        switch ($type) {
            case 'file':
                rename($this->testPath . '/engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap-.php', $this->testPath . '/engine/Shopware/Plugins/Default/Frontend/TagCloud/Bootstrap.php');
                break;
            case 'folder':
                chmod($this->testPath . '/media/music', 0777);
                break;
        }
    }
}
