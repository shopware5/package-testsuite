<?php

declare(strict_types=1);

namespace Shopware\Component\Form;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;
use Shopware\Page\ContextAwarePage;

trait FormFillerTrait
{
    /**
     * Fill a given form where the fields are identified by their name and tag types
     *
     * @param array<array{name: string, value: mixed, type: string}> $formData Expected format: [['name' => 'sAGB', 'value' => '1', 'type' => 'checkbox']]
     */
    public function fillForm(ContextAwarePage $page, array $formData): void
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
     */
    private function getElementByName(ContextAwarePage $page, string $xpath): NodeElement
    {
        return $page->waitForSelectorPresent('xpath', $xpath);
    }

    /**
     * Helper method that checks if a given checkbox identified by name is checked
     */
    private function isCheckboxChecked(ContextAwarePage $page, string $inputName): bool
    {
        try {
            return (bool) $page->find('css', 'input[type="checkbox"][name="' . $inputName . '"]:checked');
        } catch (ElementNotFoundException $e) {
            return false;
        }
    }

    private function selectLastElement(string $xpath): string
    {
        return \sprintf('(%s)[last()]', $xpath);
    }
}
