<?php

namespace Shopware\Component\XpathBuilder;

class FrontendXpathBuilder extends BaseXpathBuilder
{
    /**
     * Returns xpath that selects an input field with an exact id
     *
     * @param string $id
     * @return string
     */
    public static function getInputById($id)
    {
        return (new self)->child('input', ['@id' => $id])->getXpath();
    }

    /**
     * Returns xpath that selects a form based on its action
     *
     * @param string $action
     * @return string
     */
    public static function getFormByAction($action)
    {
        return (new self)->child('form', ['@action' => $action])->getXpath();
    }
}