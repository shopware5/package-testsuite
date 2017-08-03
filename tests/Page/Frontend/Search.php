<?php

namespace Shopware\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Search extends Page
{
    protected $path = 'search?sSearch={searchTerm}';

    /**
     * Allow verification by checking if the search landed us on a detail page
     *
     * @param array $urlParameters
     * @return bool|int
     */
    protected function verify(array $urlParameters)
    {
        return strpos($this->getHtml(), 'is--ctl-detail is--act-index');
    }
}
