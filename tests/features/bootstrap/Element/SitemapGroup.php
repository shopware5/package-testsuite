<?php

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;

/**
 * Element: SitemapGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SitemapGroup extends MultipleElement
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

    /** @var array $selector */
    protected $selector = ['css' => '.sitemap--navigation-head'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'titleLink' => 'a',
            'level1' => 'li ~ ul > li > a',
            'level2' => 'li ~ ul > li > ul > li > a'
        ];
    }

    /**
     * Returns the group title
     * @return string
     */
    public function getTitle()
    {
        return $this->getText();
    }

    /**
     * Returns the title links
     * @param NodeElement[] $element
     * @return string[]
     */
    public function getTitleLinkData(array $element)
    {
        /** @var NodeElement $titleLink */
        $titleLink = $element[0];

        return [
            'title' => $titleLink->getAttribute('title'),
            'link' => $titleLink->getAttribute('href')
        ];
    }

    /**
     * Returns the data of entries on 1st level
     * @param NodeElement[] $elements
     * @return array[]
     */
    public function getLevel1Data(array $elements)
    {
        $result = [];

        /** @var NodeElement $element */
        foreach ($elements as $element) {
            $result[] = [
                'value' => $element->getText(),
                'title' => $element->getAttribute('title'),
                'link' => $element->getAttribute('href')
            ];
        }

        return $result;
    }

    /**
     * Returns the data of entries on 2nd level
     * @param NodeElement[] $elements
     * @return array[]
     */
    public function getLevel2Data(array $elements)
    {
        return $this->getLevel1Data($elements);
    }
}
