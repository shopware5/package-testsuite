<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends Element implements HelperSelectorInterface
{

    /**
     * Returns an array of all xpath selectors of the element/page
     *
     * Example:
     * return [
     *  'loginform' = "//input[@id='email']/ancestor::form[1]",
     *  'loginemail' = "//input[@name='email']",
     *  'password' = "//input[@name='password']",
     * ]
     *
     * @return string[]
     */
    public function getXPathSelectors()
    {
        return [];
    }

    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.top-bar--language select.language--select'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'languages' => 'option'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Returns the current language
     * Use this only for asserts. If you only need the current language, use Helper::getCurrentLanguage().
     * @return string
     */
    public function getCurrentLanguage()
    {
        $languageKeys = array(1 => 'de', 2 => 'en');

        $languages = $this->findAll('css', Helper::getRequiredSelector($this, 'languages'));

        /** @var Element $language */
        foreach ($languages as $language) {
            if ($language->getAttribute('selected')) {
                return $languageKeys[$language->getAttribute('value')];
            }
        }

        return 'de';
    }

    /**
     * Changes the language
     * @param string $language
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function setLanguage($language)
    {
        $this->selectOption($language);
        Helper::setCurrentLanguage($language);
    }
}
