<?php

namespace Shopware\Component\XpathBuilder;

class BaseXpathBuilder
{
    /** @var string $xpath */
    private $xpath;

    /**
     * BaseXpathBuilder constructor.
     * @param string $xpath
     */
    public function __construct($xpath = '/')
    {
        $this->xpath = $xpath;
    }

    /**
     * Create and return empty Xpath Builder instance
     *
     * @param string $xpath
     * @return BaseXpathBuilder
     */
    public static function create($xpath = '/')
    {
        return new self($xpath);
    }

    /**
     * Get built xpath
     *
     * @return string
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * Replace the built xpath
     *
     * @param $xpath
     */
    public function setXpath($xpath)
    {
        $this->xpath = $xpath;
    }

    /**
     * Explicitly reset the builder to start from anywhere
     * @param string $xpath
     * @return $this
     */
    public function reset($xpath = '/')
    {
        $this->setXpath($xpath);

        return $this;
    }

    /**
     * Refine current xpath by a child selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function child($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/', $conditions, $index);
    }

    /**
     * Refine current xpath by an ancestor selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function ancestor($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/ancestor::', $conditions, $index);
    }

    /**
     * Refine current xpath by a descendant selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function descendant($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/descendant::', $conditions, $index);
    }

    /**
     * Refine current xpath by a following selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function following($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/following::', $conditions, $index);
    }

    /**
     * Refine current xpath by a preceding selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function preceding($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/preceding::', $conditions, $index);
    }

    /**
     * Refine current xpath by a following sibling selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function followingSibling($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/following-sibling::', $conditions, $index);
    }

    /**
     * Refine current xpath by a preceding sibling selector
     *
     * @param string $tag
     * @param array $conditions
     * @param int|null $index
     * @return self
     */
    public function precedingSibling($tag, array $conditions = [], $index = null)
    {
        return $this->appendPartialPath($tag, '/preceding-sibling::', $conditions, $index);
    }

    public function contains($text)
    {
        if($text === '') {
            return $this;
        }

        $this->xpath .= sprintf("[text()[contains(.,'%s')]]", $text);

        return $this;
    }

    /**
     * @param string[]|string $string
     * @param string $attribute
     * @return string
     */
    public static function getContainsAttributeString($string, $attribute)
    {
        if (!is_array($string)) {
            $string = [$string];
        }
        $result = '';
        foreach ($string as $part) {
            $result .= "contains(concat(' ', normalize-space(@$attribute), ' '), ' $part ') and ";
        }
        return rtrim($result, ' and ');
    }

    /**
     * Parse an array of conditions into a xpath-readable predicate-string
     *
     * @internal
     * @param $conditions
     * @return string
     * @throws \Exception
     */
    protected function parseConditions($conditions)
    {
        $conditionString = '';

        $targetModifiersPrefixes = ['!'];
        $targetModifiers = ['@', '~'];
        $subConditionHandlers = ['starts-with', 'ends-with', 'visible'];

        foreach ($conditions as $target => $condition) {
            if (in_array($target, $subConditionHandlers, true)) {
                switch ($target) {
                    case 'starts-with':
                        if (!is_array($condition) || count($condition) !== 2) {
                            throw new \Exception('Invalid number of arguments for SubConditionHandler starts-with: '
                                . "\n" . print_r($target, true)
                                . "\n" . print_r($condition, true)
                                . "\n" . print_r($conditions, true) . "\n");
                        }
                        $conditionString .= $target . "(" . $condition[0] . ", '" . $condition[1] . "') ";
                        break;
                    case 'ends-with':
                        if (!is_array($condition) || count($condition) !== 2) {
                            throw new \Exception('Invalid number of arguments for SubConditionHandler: ' . $target);
                        }
                        $attr = $condition[0];
                        $text = $condition[1];
                        $conditionString .= "'" . $text . "'=substring(" . $attr . ", string-length(" . $attr . ")- string-length('" . $text . "') +1) ";
                        break;
                    default:
                        throw new \Exception('SubConditionHandler not implemented: ' . $target);
                }
                continue;
            }

            $targetModifiersPrefix = substr($target, 0, 1);
            if (in_array($targetModifiersPrefix, $targetModifiersPrefixes)) {
                $target = substr($target, 1);
            } else {
                $targetModifiersPrefix = null;
            }

            $targetModifier = substr($target, 0, 1);
            if (in_array($targetModifier, $targetModifiers)) {
                $target = substr($target, 1);
            } else {
                $targetModifier = null;
            }

            if ($targetModifiersPrefix) {
                switch ($targetModifiersPrefix) {
                    case '!':
                        $conditionString .= 'not(';
                        break;
                    default:
                        throw new \Exception('TargetModifiersPrefixHandler not implemented: ' . $targetModifiersPrefix);
                }
            }

            switch ($targetModifier) {
                case '@':
                    $conditionString .= $this->equals($target, $condition) . ' ';
                    break;
                case '~':
                    $conditionString .= $this->getContainsString($target, $condition) . ' ';
                    break;
                default:
                    $conditionString .= $condition . ' ';
            }

            if ($targetModifiersPrefix) {
                switch ($targetModifiersPrefix) {
                    case '!':
                        $conditionString .= ')';
                        break;
                }
            }
        }
        return $conditionString;
    }

    /**
     * Internal helper function to append a new search selector to the xpath
     * that is being built.
     *
     * @param string $tag
     * @param string $prefix
     * @param array $conditions
     * @param int $index
     * @return self
     * @throws \Exception
     */
    private function appendPartialPath($tag, $prefix, $conditions, $index)
    {
        // Input validation
        if ('' === $tag) {
            throw new \Exception('Invalid argument: Tag cannot be empty.');
        }
        if (null !== $index && !is_int($index)) {
            throw new \Exception('Invalid argument: Index must be of type integer.');
        }

        // Add prefix
        $this->xpath .= $prefix;

        // Add tag
        $this->xpath .= $tag;

        // Add conditions
        $conditionString = $this->parseConditions($conditions);
        if (!empty($conditionString)) {
            $this->xpath .= sprintf('[%s]', trim($conditionString));
        }

        // Add index
        if (null !== $index) {
            $this->xpath .= sprintf('[%d]', $index);
        }

        return $this;
    }

    /**
     * Helper function for the predicate parser
     *
     * @param $target
     * @param $text
     * @return string
     */
    private function equals($target, $text)
    {
        switch ($target) {
            case 'text':
                return "text()='$text'";
                break;
            default:
                return "@$target='$text'";
        }
    }

    /**
     * Helper function for the predicate parser
     *
     * @param string $target
     * @param string[]|string $text
     * @return string
     */
    private function getContainsString($target, $text)
    {
        if (!is_array($text)) {
            $text = [$text];
        }

        switch ($target) {
            case 'text':
                $result = '';
                foreach ($text as $part) {
                    $result .= "./descendant-or-self::*[text()[contains(.,'$part')]] and ";
                }
                return rtrim($result, ' and ');
                break;
            default:
                return self::getContainsAttributeString($text, $target);
        }
    }
}