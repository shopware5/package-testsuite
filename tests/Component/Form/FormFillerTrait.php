<?php

namespace Shopware\Component\Form;

use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

trait FormFillerTrait
{
    /**
     * Fill a given form where the fields are identified by their name and tag types
     *
     * @param array $formData Expected format: [['name' => 'sAGB', 'value' => '1', 'type' => 'checkbox']]
     */
    public function fillForm(ContextAwarePage $page, array $formData)
    {
        foreach ($formData as $formElement) {
            switch ($formElement['type']) {
                case 'input':
                    $this->getElementByName($page, FrontendXpathBuilder::getElementXpathByName('input', $formElement['name']))->setValue($formElement['value']);
                    break;
                case 'select':
                    $this->getElementByName($page, FrontendXpathBuilder::getElementXpathByName('select', $formElement['name']))->selectOption($formElement['value']);
                    break;
                case 'checkbox':
                    if ($this->isCheckboxChecked($page, $formElement['name']) !== (bool) $formElement['value']) {
                        $xpath = FrontendXpathBuilder::getElementXpathByName('input', $formElement['name']);
                        $this->getElementByName($page, $this->selectLastElement($xpath))->check();
                    }
                    break;
            }
        }
    }

    /**
     * Get a NodeElement with the given name
     *
     * @param string $xpath
     *
     * @return \Behat\Mink\Element\NodeElement|null
     */
    private function getElementByName(ContextAwarePage $page, $xpath)
    {
        return $page->waitForSelectorPresent('xpath', $xpath);
    }

    /**
     * Helper method that checks if a given checkbox identified by name is checked
     *
     * @param string $inputName
     *
     * @return bool
     */
    private function isCheckboxChecked(ContextAwarePage $page, $inputName)
    {
        return (bool) $page->find('css', 'input[type="checkbox"][name="' . $inputName . '"]:checked');
    }

    /**
     * @param string $xpath
     *
     * @return string
     */
    private function selectLastElement($xpath)
    {
        return sprintf('(%s)[last()]', $xpath);
    }
}
