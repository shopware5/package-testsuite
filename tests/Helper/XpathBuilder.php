<?php

namespace Shopware\Helper;

use Shopware\Tests\Mink\Helper;

/**
 * Class XpathBuilder
 *
 * This class generates an XPath string via a fluent interface.
 * The Syntax is:
 *
 * $xp = new XpathBuilder();
 * $xpath = $xp->tag(<direction>,[<conditions>], <position>)->...->get()
 *
 * <direction> and <position> are optional.
 * Condition modifiers are @ and ~, where @ means exact and ~ means contains
 * Example:
 *
 * $xp->p(['~text' => 'Example Text'])->table('asc', ['@class' => 'foo', 'and', '@data-attribute' => 'bar'])->div('desc', [], 1)->get();
 *
 * This will create the string:
 *
 * //p[contains(text(),'Example Text')]/ancestor::table[@class='foo' and @data-attribute='bar']/descendant::div[1]
 *
 * Any HTML or XML Tag can be used as function name since these are handled via __call
 *
 * @package Shopware\Tests\Mink
 * @method XpathBuilder div(...$args)
 * @method XpathBuilder a(...$args)
 * @method XpathBuilder strong(...$args)
 * @method XpathBuilder span(...$args)
 * @method XpathBuilder spanWithText(...$args)
 * @method XpathBuilder ul(...$args)
 * @method XpathBuilder li(...$args)
 * @method XpathBuilder liWithText(...$args)
 * @method XpathBuilder img(...$args)
 * @method XpathBuilder nav(...$args)
 * @method XpathBuilder fieldset(...$args)
 * @method XpathBuilder button(...$args)
 * @method XpathBuilder input(...$args)
 * @method XpathBuilder label(...$args)
 * @method XpathBuilder table(...$args)
 * @method XpathBuilder tr(...$args)
 * @method XpathBuilder td(...$args)
 */
class XpathBuilder
{
    /** @var string */
    private $path;

    /**
     * @param string[]|string $string
     * @return string
     */
    public static function getContainsClassString($string)
    {
        return self::getContainsAttributeString($string, 'class');
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

    public function __construct()
    {
        $this->path = '';
    }

    /**
     * @param string $tag
     * @param array $conditions
     * @return XpathBuilder
     */
    public function __call($tag, array $conditions)
    {
        $tag = strtolower($tag);
        $path = '';

        if (Helper::endswith($tag, 'withtext')) {
            $tag = substr($tag, 0, -1 * strlen('withtext'));
            $text = $conditions[0];

            $this->path .= "/text()[contains(.,'" . $text . "')]/ancestor::*[self::" . $tag . "][1]";
            return $this;
        }

        $conditionCount = count($conditions);

        if ($conditionCount === 0) {
            $this->path .= '/' . $tag;
            return $this;
        }

        if ($conditionCount > 1) {
            $prefix = array_shift($conditions);
            $path = $this->expandPrefix($prefix) . $tag;
        }

        if (strlen($path) === 0) {
            $path .= '/' . $tag;
        }

        $conditionString = $this->parseConditions($conditions[0]);

        if (!empty($conditionString)) {
            $path .= "[" . trim($conditionString) . "]";
        }

        if ($conditionCount === 3 && is_int($conditions[1])) {
            $path .= "[" . $conditions[1] . "]";
        }

        $this->path .= $path;

        return $this;
    }

    /**
     * @param string $target
     * @param string[]|string $text
     * @return string
     */
    protected function getContainsString($target, $text)
    {
        if (!is_array($text)) {
            $text = [$text];
        }

        switch ($target) {
            case 'text':
                $result = '';
                foreach ($text as $part) {
                    $result .= "./descendant-or-self::*[contains(text(),'$part')] and ";
                }
                return rtrim($result, ' and ');
                break;
            default:
                return self::getContainsAttributeString($text, $target);
        }
    }

    protected function equals($target, $text)
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
     * @param bool $reset If true (default), the current path will be emptied
     * @return string
     */
    public function get($reset = true)
    {
        $output = $this->path;
        if ($reset === true) {
            $this->path = '';
        }

        $pathStart = substr($output, 0, 10);
        if ($pathStart !== '/ancestor:' && $pathStart != '/descendan') {
            $output = '/' . $output;
        }

        return $output;
    }

    public function __toString()
    {
        return $this->get();
    }

    /**
     * @param string $title
     * @return XpathBuilder
     */
    public function xWindowByTitle($title)
    {
        return $this->span(['~text' => $title])->div('asc', ['~class' => 'x-window'], 1);
    }

    /**
     * @param string $title
     * @return XpathBuilder
     */
    public function xWindowByExactTitle($title)
    {
        return $this->span(['@text' => $title])->div('asc', ['~class' => 'x-window'], 1);
    }

    private function parseConditions($conditions)
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
                                ."\n".print_r($target, true)
                                ."\n".print_r($condition, true)
                                ."\n".print_r($conditions, true)."\n");
                        }
                        $conditionString .= $target."(".$condition[0].", '".$condition[1]."') ";
                        break;
                    case 'ends-with':
                        if (!is_array($condition) || count($condition) !== 2) {
                            throw new \Exception('Invalid number of arguments for SubConditionHandler: '.$target);
                        }
                        $attr = $condition[0];
                        $text = $condition[1];
                        $conditionString .= "'".$text."'=substring(".$attr.", string-length(".$attr.")- string-length('".$text."') +1) ";
                        break;
                    default:
                        throw new \Exception('SubConditionHandler not implemented: '.$target);
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

    public function getXTabContainerForLabel($label)
    {
        return $this->span('desc', ['@text' => $label])->div('asc',['~class' => 'x-tab'],1)->get();
    }

    public function getXSelectorPebbleForLabel($label)
    {
        return $this->label('desc', ['@text' => $label])
            ->td('asc', [], 1)
            ->td('fs', [], 1)
            ->div('desc', ['~class' => 'x-form-trigger'])
            ->get();
    }

    /**
     * @param $action
     * @return XpathBuilder
     */
    public function xDropdown($action)
    {
        return $this
            ->div(['~class' => 'x-boundlist', 'and', '@data-action' => $action])
            ->li('desc', ['@role' => 'option']);
    }

    public function getXInputForLabel($label)
    {
        return $this->getXFormElementForLabel($label, 'input');
    }

    public function getXTextareaForLabel($label)
    {
        return $this->getXFormElementForLabel($label, 'textarea');
    }

    public function getXFormElementForLabel($label, $tag)
    {
        return $this->label('desc', ['@text' => $label])
            ->td('asc', [], 1)
            ->td('fs', [], 1)
            ->$tag('desc', [])
            ->get();
    }

    public function getXFocussedInput()
    {
        return $this
            ->input(['~class' => 'x-form-focus'])
            ->get();
    }

    /**
     * @param string $prefix
     * @return string
     */
    private function expandPrefix($prefix)
    {
        switch ($prefix) {
            case null:
            case '':
                return '/';
            case 'ancestor':
            case 'asc':
                return '/ancestor::';
            case 'descendant':
            case 'dsc':
            case 'desc':
                return '/descendant::';
            case 'following-sibling':
            case 'fs':
                return '/following-sibling::';
            case 'preceding-sibling':
            case 'ps':
                return '/preceding-sibling::';
            case 'following':
            case 'f':
                return '/following::';
                break;
            case 'preceding':
            case 'p':
                return '/preceding::';
                break;
            default:
                throw new \InvalidArgumentException(sprintf("Unknown modifier: %s", $prefix));
        }
    }

    public function getXPencilIcon($direction = 'desc')
    {
        return $this->img($direction, ['~class' => 'sprite-pencil'])->get();
    }

    public function getXGridBodyForLabel($label)
    {
        return $this
            ->span('desc', ['~class' => 'x-panel-header-text', 'and', '@text' => $label])
            ->div('asc', ['~class' => 'x-grid-with-row-lines'])
            ->div('desc', ['~class' => 'x-grid-body'])
            ->get();
    }
}
