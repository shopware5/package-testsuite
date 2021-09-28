<?php

declare(strict_types=1);

namespace Shopware\Context\Exception;

class PageNotDefinedException extends \RuntimeException
{
    /**
     * @param class-string $pageClass
     */
    public function __construct(string $pageName, string $pageClass, int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf('Page "%s" of "%s" is not defined', $pageName, $pageClass);
        parent::__construct($message, $code, $previous);
    }
}
