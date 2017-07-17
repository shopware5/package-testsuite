<?php

namespace Shopware\Tests\Mink;

use \Behat\Behat\Tester\Exception\PendingException;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Component\Helper\HelperSelectorInterface;
use Shopware\Tests\Mink\Element\MultipleElement;

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
     * @throws \Exception
     */
    public static function checkArray(array $check, $strict = false)
    {
        foreach ($check as $key => $comparison) {
            if ((!is_array($comparison)) || (count($comparison) != 2)) {
                self::throwException('Each comparison have to be an array with exactly two values!');
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
     * @param bool $throwExceptions
     * @return Element[]
     * @throws \Exception|PendingException
     */
    public static function findElements(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
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

        if ($throwExceptions) {
            $messages = ['The following elements of ' . get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (count($messages) > 1) {
                self::throwException($messages);
            }
        }

        return $elements;
    }

    /**
     * Finds all elements of their selectors
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @param bool $throwExceptions
     * @return array
     * @throws \Exception|PendingException
     */
    public static function findAllOfElements(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
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

        if ($throwExceptions) {
            $messages = ['The following elements of ' . get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (count($messages) > 1) {
                self::throwException($messages);
            }
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

        self::throwException($message, self::EXCEPTION_PENDING);
    }

    const EXCEPTION_GENERIC = 1;
    const EXCEPTION_PENDING = 2;

    /**
     * Throws a generic or pending exception, shows the backtrace to the first context class call
     * @deprecated
     * @param array|string $messages
     * @param int $type
     * @throws \Exception|PendingException
     */
    public static function throwException($messages = [], $type = self::EXCEPTION_GENERIC)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $message = [<<<EOD
Exception thrown in {$debug[1]['class']}{$debug[1]['type']}{$debug[1]['function']}():{$debug[0]['line']}

Stacktrace:
EOD
        ];

        foreach ($debug as $key => $call) {
            $next = $debug[$key + 1];

            if (!isset($next['class'])) {
                break;
            }

            $message[] = "{$next['class']}{$next['type']}{$next['function']}():{$call['line']}";
        }

        $message[] = "\r\nException:";

        $messages = array_merge($message, $messages);
        $message = implode("\r\n", $messages);

        switch ($type) {
            case self::EXCEPTION_GENERIC:
                throw new \Exception($message);
                break;

            case self::EXCEPTION_PENDING:
                throw new PendingException($message);
                break;

            default:
                self::throwException('Invalid exception type!', self::EXCEPTION_PENDING);
                break;
        }
    }

    /**
     * Clicks the requested named link
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @throws \Exception
     * @throws PendingException
     */
    public static function clickNamedLink(HelperSelectorInterface $parent, $key)
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $language = 'de';

        $parent->clickLink($locatorArray[$key][$language]);
    }

    /**
     * Presses the requested named button
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     */
    public static function pressNamedButton(HelperSelectorInterface $parent, $key)
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $language = 'de';
        $parent->pressButton($locatorArray[$key][$language]);
    }

    /**
     * Helper method that returns the content block of a page
     * @deprecated
     * @param Page $parent
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    private static function getContentBlock(Page $parent)
    {
        $contentBlocks = [
            'emotion' => 'div#content > div.inner',
            'responsive' => 'div.content-main--inner'
        ];

        foreach ($contentBlocks as $locator) {
            $block = $parent->find('css', $locator);

            if ($block) {
                return $block;
            }
        }

        self::throwException('No content block found!');
    }

    /**
     * Fills a the inputs of a form
     * @deprecated
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $formKey
     * @param array $values
     * @param array $callables Array of callable functions ['formFieldName' => function(NodeElement $form, $fieldValue){}]
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
                    self::throwException($message);
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
