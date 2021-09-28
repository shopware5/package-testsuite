<?php

declare(strict_types=1);

namespace Shopware\Context\Exception;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Session;

class TooManyElementsFoundException extends ExpectationException
{
    /**
     * @param DriverInterface|Session $driver driver instance
     */
    public function __construct($driver, ?string $type = null, ?string $selector = null, ?string $locator = null)
    {
        $message = '';

        if ($type !== null) {
            $message .= ucfirst($type);
        } else {
            $message .= 'Tag';
        }

        if ($locator !== null) {
            if ($selector === null || \in_array($selector, ['css', 'xpath'])) {
                $selector = 'matching ' . ($selector ?: 'locator');
            } else {
                $selector = 'with ' . $selector;
            }
            $message .= ' ' . $selector . ' "' . $locator . '"';
        }

        $message .= ' found too often. The selector should be more specific.';

        parent::__construct($message, $driver);
    }
}
