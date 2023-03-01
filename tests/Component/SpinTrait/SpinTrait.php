<?php

declare(strict_types=1);

namespace Shopware\Component\SpinTrait;

trait SpinTrait
{
    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @param callable $lambda
     *
     * @throws \Exception
     */
    protected function spin($lambda, int $wait = 10): void
    {
        if (!$this->spinWithNoException($lambda, $wait)) {
            throw new \Exception(sprintf('Spin function timed out after %s seconds', $wait));
        }
    }

    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @param callable $lambda
     */
    protected function spinWithNoException($lambda, int $wait = 10): bool
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        return false;
    }
}
