<?php

declare(strict_types=1);

namespace Shopware\Context\Exception;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PageNotDefinedException extends \RuntimeException
{
    /**
     * @param class-string<Page> $pageClass
     */
    public function __construct(string $pageClass, int $code = 0, ?\Throwable $previous = null)
    {
        $message = \sprintf('Page "%s" is not defined', $pageClass);
        parent::__construct($message, $code, $previous);
    }
}
