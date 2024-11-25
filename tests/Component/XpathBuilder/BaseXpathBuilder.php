<?php

declare(strict_types=1);

namespace Shopware\Component\XpathBuilder;

class BaseXpathBuilder
{
    private string $xpath;

    public function __construct(string $xpath = '/')
    {
        $this->xpath = $xpath;
    }

    /**
     * Create and return empty Xpath Builder instance
     *
     * @return BaseXpathBuilder
     */
    public static function create(string $xpath = '/')
    {
        return new self($xpath);
    }

    /**
     * Get built xpath
     */
    public function getXpath(): string
    {
        return $this->xpath;
    }

    /**
     * Replace the built xpath
     */
    public function setXpath(string $xpath): void
    {
        $this->xpath = $xpath;
    }

    /**
     * Explicitly reset the builder to start from anywhere
     *
     * @return $this
     */
    public function reset(string $xpath = '/'): BaseXpathBuilder
    {
        $this->setXpath($xpath);

        return $this;
    }

    /**
     * Refine current xpath by a child selector
     */
    public function child(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/', $conditions, $index);
    }

    /**
     * Refine current xpath by an ancestor selector
     */
    public function ancestor(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/ancestor::', $conditions, $index);
    }

    /**
     * Refine current xpath by a descendant selector
     */
    public function descendant(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/descendant::', $conditions, $index);
    }

    /**
     * Refine current xpath by a following selector
     */
    public function following(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/following::', $conditions, $index);
    }

    /**
     * Refine current xpath by a preceding selector
     */
    public function preceding(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/preceding::', $conditions, $index);
    }

    /**
     * Refine current xpath by a following sibling selector
     */
    public function followingSibling(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/following-sibling::', $conditions, $index);
    }

    /**
     * Refine current xpath by a preceding sibling selector
     */
    public function precedingSibling(string $tag, array $conditions = [], ?int $index = null): BaseXpathBuilder
    {
        return $this->appendPartialPath($tag, '/preceding-sibling::', $conditions, $index);
    }

    public function contains(string $text): BaseXpathBuilder
    {
        if ($text === '') {
            return $this;
        }

        $this->xpath .= \sprintf("[text()[contains(.,'%s')]]", $text);

        return $this;
    }

    /**
     * @param string[]|string $string
     */
    public static function getContainsAttributeString($string, string $attribute): string
    {
        if (!\is_array($string)) {
            $string = [$string];
        }
        $result = '';
        foreach ($string as $part) {
            $result .= \sprintf("contains(concat(' ', normalize-space(@%s), ' '), ' %s ') and ", $attribute, $part);
        }

        return rtrim($result, ' and ');
    }

    /**
     * Parse an array of conditions into a xpath-readable predicate-string
     *
     * @internal
     *
     * @param array<array-key, string|array<string>> $conditions
     *
     * @throws \Exception
     */
    protected function parseConditions(array $conditions): string
    {
        $conditionString = '';

        $targetModifiers = ['@', '~'];
        $subConditionHandlers = ['starts-with', 'ends-with', 'visible'];

        foreach ($conditions as $target => $condition) {
            $target = (string) $target;
            if (\in_array($target, $subConditionHandlers, true)) {
                switch ($target) {
                    case 'starts-with':
                        if (!\is_array($condition) || \count($condition) !== 2) {
                            throw new \Exception('Invalid number of arguments for SubConditionHandler starts-with: '
                                . "\n" . print_r($target, true)
                                . "\n" . print_r($condition, true)
                                . "\n" . print_r($conditions, true) . "\n");
                        }
                        $conditionString .= $target . '(' . $condition[0] . ", '" . $condition[1] . "') ";
                        break;
                    case 'ends-with':
                        if (!\is_array($condition) || \count($condition) !== 2) {
                            throw new \Exception('Invalid number of arguments for SubConditionHandler: ' . $target);
                        }
                        $attr = $condition[0];
                        $text = $condition[1];
                        $conditionString .= "'" . $text . "'=substring(" . $attr . ', string-length(' . $attr . ")- string-length('" . $text . "') +1) ";
                        break;
                    default:
                        throw new \Exception('SubConditionHandler not implemented: ' . $target);
                }
                continue;
            }

            $targetModifiersPrefix = substr($target, 0, 1);
            if ($targetModifiersPrefix === '!') {
                $target = substr($target, 1);
            } else {
                $targetModifiersPrefix = null;
            }

            $targetModifier = substr($target, 0, 1);
            if (\in_array($targetModifier, $targetModifiers, true)) {
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
                    if (!\is_string($condition)) {
                        throw new \RuntimeException('condition must be string at this point');
                    }
                    $conditionString .= $this->equals($target, $condition) . ' ';
                    break;
                case '~':
                    $conditionString .= $this->getContainsString($target, $condition) . ' ';
                    break;
                default:
                    if (!\is_string($condition)) {
                        throw new \RuntimeException('condition must be string at this point');
                    }
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
     * @throws \Exception
     */
    private function appendPartialPath(string $tag, string $prefix, array $conditions, ?int $index): BaseXpathBuilder
    {
        // Input validation
        if ($tag === '') {
            throw new \Exception('Invalid argument: Tag cannot be empty.');
        }

        // Add prefix
        $this->xpath .= $prefix;

        // Add tag
        $this->xpath .= $tag;

        // Add conditions
        $conditionString = $this->parseConditions($conditions);
        if (!empty($conditionString)) {
            $this->xpath .= \sprintf('[%s]', trim($conditionString));
        }

        // Add index
        if ($index !== null) {
            $this->xpath .= \sprintf('[%d]', $index);
        }

        return $this;
    }

    /**
     * Helper function for the predicate parser
     */
    private function equals(string $target, string $text): string
    {
        switch ($target) {
            case 'text':
                return \sprintf("text()='%s'", $text);
            default:
                return \sprintf("@%s='%s'", $target, $text);
        }
    }

    /**
     * Helper function for the predicate parser
     *
     * @param string[]|string $text
     */
    private function getContainsString(string $target, $text): string
    {
        if (!\is_array($text)) {
            $text = [$text];
        }

        switch ($target) {
            case 'text':
                $result = '';
                foreach ($text as $part) {
                    $result .= "./descendant-or-self::*[text()[contains(.,'$part')]] and ";
                }

                return rtrim($result, ' and ');
            default:
                return self::getContainsAttributeString($text, $target);
        }
    }
}
