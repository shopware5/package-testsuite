<?php
/**
 * Created by PhpStorm.
 * User: niklas
 * Date: 24.10.16
 * Time: 15:08
 */

namespace Shopware\Helper;

trait SpinTrait
{
    /**
     * Based on Behat's own example
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     * @param $lambda
     * @param int $wait
     * @return bool
     * @throws \Exception
     */
    protected function spin($lambda, $wait = 60)
    {
        if (!$this->spinWithNoException($lambda, $wait)) {
            throw new \Exception("Spin function timed out after {$wait} seconds");
        }
    }

    /**
     * Based on Behat's own example
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     * @param $lambda
     * @param int $wait
     * @return bool
     */
    protected function spinWithNoException($lambda, $wait = 60)
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
