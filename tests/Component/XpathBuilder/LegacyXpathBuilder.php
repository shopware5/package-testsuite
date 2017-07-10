<?php

namespace Shopware\Component\XpathBuilder;

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
 * @deprecated
 * @package Shopware\Tests\Mink
 * @method LegacyXpathBuilder div(...$args)
 * @method LegacyXpathBuilder a(...$args)
 * @method LegacyXpathBuilder strong(...$args)
 * @method LegacyXpathBuilder span(...$args)
 * @method LegacyXpathBuilder spanWithText(...$args)
 * @method LegacyXpathBuilder ul(...$args)
 * @method LegacyXpathBuilder li(...$args)
 * @method LegacyXpathBuilder liWithText(...$args)
 * @method LegacyXpathBuilder img(...$args)
 * @method LegacyXpathBuilder nav(...$args)
 * @method LegacyXpathBuilder fieldset(...$args)
 * @method LegacyXpathBuilder button(...$args)
 * @method LegacyXpathBuilder input(...$args)
 * @method LegacyXpathBuilder label(...$args)
 * @method LegacyXpathBuilder table(...$args)
 * @method LegacyXpathBuilder tr(...$args)
 * @method LegacyXpathBuilder td(...$args)
 * @method LegacyXpathBuilder picture(...$args)
 * @method LegacyXpathBuilder source(...$args)
 * @method LegacyXpathBuilder form(...$args)
 * @method LegacyXpathBuilder textarea(...$args)
 */
class LegacyXpathBuilder extends BaseXpathBuilder
{
    /** @var string */
    private $path;

    /**
     * @deprecated
     * @param string[]|string $string
     * @return string
     */
    public static function getContainsClassString($string)
    {
        return self::getContainsAttributeString($string, 'class');
    }

    /**
     * LegacyXpathBuilder constructor.
     * @deprecated
     */
    public function __construct()
    {
        $this->path = '';
    }

    /**
     * @deprecated
     * @param string $tag
     * @param array $conditions
     * @return LegacyXpathBuilder
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
     * @deprecated
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
     * @deprecated
     * @param string $title
     * @return LegacyXpathBuilder
     */
    public function xWindowByTitle($title)
    {
        return $this->span(['~text' => $title])->div('asc', ['~class' => 'x-window'], 1);
    }

    /**
     * @deprecated
     * @param string $title
     * @return LegacyXpathBuilder
     */
    public function xWindowByExactTitle($title)
    {
        return $this->span(['@text' => $title])->div('asc', ['~class' => 'x-window'], 1);
    }

    /**
     * @deprecated
     * @param $label
     * @return string
     */
    public function getXTabContainerForLabel($label)
    {
        return $this->span('desc', ['@text' => $label])->div('asc', ['~class' => 'x-tab'], 1)->get();
    }

    /**
     * @deprecated
     * @param $label
     * @return string
     */
    public function getXSelectorPebbleForLabel($label)
    {
        return $this->label('desc', ['@text' => $label])
            ->td('asc', [], 1)
            ->td('fs', [], 1)
            ->div('desc', ['~class' => 'x-form-trigger'])
            ->get();
    }

    /**
     * @deprecated
     * @param string $action
     * @param string $optionText
     * @return LegacyXpathBuilder
     */
    public function xDropdown($action, $optionText = "")
    {
        $this->div(['~class' => 'x-boundlist', 'and', '@data-action' => $action]);
        if (empty($optionText)) {
            $this->li('desc', ['@role' => 'option']);
            return $this;
        }
        $this->li('desc', ['@role' => 'option', 'and', '@text' => $optionText]);
        return $this;
    }

    /**
     * @deprecated
     * @param $label
     * @return mixed
     */
    public function getXInputForLabel($label)
    {
        return $this->getXFormElementForLabel($label, 'input');
    }

    /**
     * @deprecated
     * @param $label
     * @return mixed
     */
    public function getXTextareaForLabel($label)
    {
        return $this->getXFormElementForLabel($label, 'textarea');
    }

    /**
     * @deprecated
     * @param $label
     * @param $tag
     * @return mixed
     */
    public function getXFormElementForLabel($label, $tag)
    {
        return $this->label('desc', ['@text' => $label])
            ->td('asc', [], 1)
            ->td('fs', [], 1)
            ->$tag('desc', [])
            ->get();
    }

    /**
     * @deprecated
     * @return string
     */
    public function getXFocussedInput()
    {
        return $this
            ->input(['~class' => 'x-form-focus'])
            ->get();
    }

    /**
     * @deprecated
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

    /**
     * @deprecated
     * @param string $direction
     * @return string
     */
    public function getXPencilIcon($direction = 'desc')
    {
        return $this->img($direction, ['~class' => 'sprite-pencil'])->get();
    }

    /**
     * @deprecated
     * @param string $direction
     * @return string
     */
    public function getXMinusIcon($direction = 'desc')
    {
        return $this->img($direction, ['~class' => 'sprite-minus-circle-frame'])->get();
    }

    /**
     * @deprecated
     * @param $label
     * @return string
     */
    public function getXGridBodyForLabel($label)
    {
        return $this
            ->span('desc', ['~class' => 'x-panel-header-text', 'and', '@text' => $label])
            ->div('asc', ['~class' => 'x-grid-with-row-lines'])
            ->div('desc', ['~class' => 'x-grid-body'])
            ->get();
    }

    /**
     * @deprecated
     * @param string $selectString
     * @return array
     */
    public function getSelectTreeElements($selectString)
    {
        $xpaths = [];
        $categories = array_map('trim', explode('>', $selectString));
        $lastKey = count($categories) - 1;

        foreach ($categories as $key => $category) {
            if ($key == $lastKey) {
                $categoryXP = $this->div('desc', ['@text' => $category])->img('desc', ['~class' => 'x-tree-icon'])->get();
                $xpaths[] = $categoryXP;
                break;
            }
            $categoryXP = $this->div('desc', ['~class' => 'x-tree-panel'])->div('desc', ['@text' => $category])->img('desc', ['~class' => 'x-tree-expander'])->get();
            $xpaths[] = $categoryXP;
        }
        return $xpaths;
    }
}
