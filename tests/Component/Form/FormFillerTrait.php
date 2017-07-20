<?php

namespace Shopware\Component\Form;

use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

trait FormFillerTrait
{
    /**
     * Fill a given form where the fields are identified by their name and tag types
     *
     * @param ContextAwarePage $page
     * @param array $formData Expected format: [['name' => 'sAGB', 'value' => '1', 'type' => 'checkbox']]
     */
    public function fillForm(ContextAwarePage $page, array $formData)
    {
        foreach ($formData as $formElement) {
            switch ($formElement['type']) {
                case 'input':
                    $this->getElementByName($page, 'input', $formElement['name'])->setValue($formElement['value']);
                    break;
                case 'select':
                    $this->getElementByName($page, 'select', $formElement['name'])->selectOption($formElement['value']);
                    break;
                case 'checkbox':
                    if ($this->isCheckboxChecked($page, $formElement['name']) !== (bool)$formElement['value']) {
                        $this->getElementByName($page, 'input', $formElement['name'])->check();
                    }
                    break;
            }
        }
    }

    /**
     * Get a NodeElement with the given name
     *
     * @param ContextAwarePage $page
     * @param string $type
     * @param string $name
     * @return \Behat\Mink\Element\NodeElement|null
     */
    private function getElementByName(ContextAwarePage $page, $type, $name)
    {
        $elementXpath = FrontendXpathBuilder::getElementXpathByName($type, $name);
        $page->waitForSelectorPresent('xpath', $elementXpath);
        return $page->find('xpath', $elementXpath);
    }

    /**
     * Helper method that checks if a given checkbox identified by name is checked
     *
     * @param ContextAwarePage $page
     * @param string $inputName
     * @return bool
     */
    private function isCheckboxChecked(ContextAwarePage $page, $inputName)
    {
        return (bool)$page->find('css', 'input[type="checkbox"][name="' . $inputName . '"]:checked');
    }
}