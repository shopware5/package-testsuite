<?php

namespace Shopware\Tests\Mink;

use \Behat\Behat\Tester\Exception\PendingException;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Component\Helper\HelperSelectorInterface;
use Shopware\Tests\Mink\Element\MultipleElement;

/**
 * @deprecated
 */
class Helper
{



    /**
     * Helper function to check each row of an array.
     * If each second sub-element of a row is equal or in its first, function returns true
     * If not, the key of the element will be returned (can be used for more detailed descriptions of faults)
     * Throws an exception if $check has an incorrect format
     * @param array $check
     * @param bool $strict
     * @return bool|int|string
     * @deprecated
     * @throws \Exception
     */
    public static function checkArray(array $check, $strict = false)
    {
        foreach ($check as $key => $comparison) {
            if ((!is_array($comparison)) || (count($comparison) != 2)) {
                throw new \Exception('Each comparison have to be an array with exactly two values!');
            }

            $comparison = array_values($comparison);

            if ($comparison[0] === $comparison[1]) {
                continue;
            }

            if ($strict || is_float($comparison[0]) || is_float($comparison[1])) {
                return $key;
            }

            $haystack = (string)$comparison[0];
            $needle = (string)$comparison[1];

            if (strlen($needle) === 0) {
                if (strlen($haystack) === 0) {
                    return true;
                }

                return $key;
            }

            if (strpos($haystack, $needle) === false) {
                return $key;
            }
        }

        return true;
    }

    /**
     * Converts the value to a float
     * @param string $value
     * @deprecated
     * @return float
     */
    public static function floatValue($value)
    {
        if (is_float($value)) {
            return $value;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $value);
        preg_match("/([0-9]+[\\.]?[0-9]*)/", $float, $matches);

        return floatval($matches[0]);
    }

    /**
     * Converts values with key in $keys to floats
     * @param array $values
     * @param array $keys
     * @deprecated
     * @return array
     */
    public static function floatArray(array $values, array $keys = [])
    {
        if (is_array(current($values))) {
            foreach ($values as &$array) {
                $array = self::floatArray($array, $keys);
            }

            return $values;
        }

        if (empty($keys)) {
            $keys = array_keys($values);
        }

        foreach ($keys as $key) {
            if (isset($values[$key])) {
                $values[$key] = self::floatValue($values[$key]);
            }
        }

        return $values;
    }


    /**
     * Finds elements by their selectors
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @return Element[]
     * @deprecated
     * @throws \Exception|PendingException
     */
    public static function findElements(HelperSelectorInterface $parent, array $keys)
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
     * Finds all elements of their selectors
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @return array
     * @throws \Exception|PendingException
     */
    public static function findAllOfElements(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $elements = [];

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->findAll('css', $locator);

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
     * @deprecated
     * @param Element $element
     * @param string $propertyName
     * @return string|float|array
     */
    private static function getElementProperty(Element $element, $propertyName)
    {
        $method = 'get' . ucfirst($propertyName) . 'Property';
        return $element->$method();
    }

    /**
     * @deprecated
     * @param array $needles
     * @param MultipleElement $haystack
     * @return array|bool
     */
    public static function assertElements(array $needles, MultipleElement $haystack)
    {
        $failures = [];

        foreach ($needles as $key => $item) {
            $element = $haystack->setInstance($key + 1);
            $result = self::assertElementProperties($element, $item);

            if ($result !== true) {
                $failures[] = [
                    'properties' => $item,
                    'result' => $result
                ];
            }
        }

        if ($failures) {
            return $failures;
        }

        return true;
    }

    /**
     * @deprecated
     * @param Element $element
     * @param array $properties
     * @return bool|array
     */
    private static function assertElementProperties(Element $element, array $properties)
    {
        $check = [];

        foreach ($properties as $propertyName => $value) {
            $property = self::getElementProperty($element, $propertyName);
            $check[$propertyName] = [$property, $value];
        }

        $result = self::checkArray($check);

        if ($result === true) {
            return true;
        }

        return [
            'key' => $result,
            'value' => $check[$result][0],
            'value2' => $check[$result][1]
        ];
    }
}
