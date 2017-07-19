<?php

namespace Shopware\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Search extends Page
{
    protected $path = 'search?sSearch={searchTerm}';
}
