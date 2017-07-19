<?php

namespace Shopware\Component\Helper;

use \Behat\Behat\Tester\Exception\PendingException;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * @deprecated
 */
class Helper
{
    /**
     * Fills a the inputs of a form
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $formKey
     * @param array $values
     * @param array $callables Array of callable functions ['formFieldName' => function(NodeElement $form, $fieldValue){}]
     * @throws \Exception
     */
    public static function fillForm(HelperSelectorInterface $parent, $formKey, $values, array $callables = [])
    {
        $elements = self::findElements($parent, [$formKey]);
        $form = $elements[$formKey];

        foreach ($values as $value) {
            $tempFieldName = $fieldName = $value['field'];
            unset($value['field']);

            foreach ($value as $key => $fieldValue) {

                if(array_key_exists($key, $callables)) {
                    $callables[$key]($form, $fieldValue);
                }

                if ($key !== 'value') {
                    $fieldName = sprintf('%s[%s]', $key, $tempFieldName);
                }

                if (strpos($fieldName, ".") !== false) {
                    $fieldName = str_replace(".", "][", $fieldName);
                }
                
                $field = $form->findField($fieldName);

                if (empty($field)) {
                    if (empty($fieldValue)) {
                        continue;
                    }

                    $message = sprintf('The form "%s" has no field "%s"!', $formKey, $fieldName);
                    throw new \Exception($message);
                }

                if (!$field->isVisible()) {
                    $parentClass = $field->find('xpath', '/ancestor::*[1]')->getAttribute('class');
                    if ($field->getTagName() != 'select' || strpos($parentClass, 'js--fancy-select') === false) {
                        continue;
                    }
                }

                $fieldTag = $field->getTagName();

                if ($fieldTag === 'textarea') {
                    $field->setValue($fieldValue);
                    continue;
                }

                $fieldType = $field->getAttribute('type');

                //Select
                if (empty($fieldType)) {
                    $field->selectOption($fieldValue);
                    continue;
                }

                //Checkbox
                if ($fieldType === 'checkbox') {
                    $field->check();
                    continue;
                }

                //Text
                $field->setValue($fieldValue);
            }
        }
    }

    /**
     * Finds elements by their selectors
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @return Element[]
     * @deprecated
     * @throws \Exception|PendingException
     */
    private static function findElements(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $elements = [];

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->find('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
            }

            $elements[$key] = $element;
        }

        return $elements;
    }

    /**
     * Returns the requested element css selectors
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @param bool $throwExceptions
     * @return array
     * @throws \Exception
     * @throws PendingException
     */
    private static function getRequiredSelectors(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
    {
        $errors = [];
        $locators = [];
        $selectors = $parent->getCssSelectors();

        foreach ($keys as $key) {
            if (!array_key_exists($key, $selectors)) {
                $errors['noSelector'][] = $key;
                continue;
            }

            if (empty($selectors[$key])) {
                $errors['emptySelector'][] = $key;
                continue;
            }

            $locators[$key] = $selectors[$key];
        }

        if (empty($errors) || !$throwExceptions) {
            return $locators;
        }

        $message = ['Following element selectors of ' . get_class($parent) . ' are wrong:'];

        if (isset($errors['noSelector'])) {
            $message[] = sprintf('%s (not defined)', implode(', ', $errors['noSelector']));
        }
        if (isset($errors['emptySelector'])) {
            $message[] = sprintf('%s (empty)', implode(', ', $errors['emptySelector']));
        }

        throw new \Exception($message);
    }
}
