<?php

declare(strict_types=1);

namespace Shopware\Component\XpathBuilder;

class FrontendXpathBuilder extends BaseXpathBuilder
{
    /**
     * Returns xpath that selects an input field with an exact id
     */
    public static function getInputById(string $id): string
    {
        return (new self())->child('input', ['@id' => $id])->getXpath();
    }

    /**
     * Return xpath to an element by its name
     */
    public static function getElementXpathByName(string $tag, string $name): string
    {
        return (new self())->child($tag, ['~name' => $name])->getXpath();
    }

    /**
     * Returns xpath that selects a form based on its action
     */
    public static function getFormByAction(string $action): string
    {
        return (new self())->child('form', ['@action' => $action])->getXpath();
    }
}
