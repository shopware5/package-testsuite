<?php

namespace Shopware\Component\XpathBuilder;

class FrontendXpathBuilder extends BaseXpathBuilder
{
    /**
     * Returns xpath that selects an input field with an exact id
     *
     * @param string $id
     *
     * @return string
     */
    public static function getInputById($id)
    {
        return (new self())->child('input', ['@id' => $id])->getXpath();
    }

    /**
     * Return xpath to an element by its name
     *
     * @param string $tag
     * @param string $name
     *
     * @return string
     */
    public static function getElementXpathByName($tag, $name)
    {
        return (new self())->child($tag, ['~name' => $name])->getXpath();
    }

    /**
     * Returns xpath that selects a form based on its action
     *
     * @param string $action
     *
     * @return string
     */
    public static function getFormByAction($action)
    {
        return (new self())->child('form', ['@action' => $action])->getXpath();
    }
}
