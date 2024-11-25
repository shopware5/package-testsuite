<?php

declare(strict_types=1);

namespace Shopware\Element\Frontend\Checkout;

class CartPosition
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $number;

    /**
     * @var int
     */
    private int $quantity;

    /**
     * @var float
     */
    private float $itemPrice;

    /**
     * @var float
     */
    private float $sum;

    /**
     * @param string $name
     * @param string $number
     * @param int    $quantity
     * @param float  $itemPrice
     * @param float  $sum
     */
    private function __construct(string $name, string $number, int $quantity, float $itemPrice, float $sum)
    {
        $this->name = $name;
        $this->number = $number;
        $this->quantity = $quantity;
        $this->itemPrice = $itemPrice;
        $this->sum = $sum;
    }

    /**
     * @throws \Exception
     *
     * @return CartPosition
     */
    public static function fromArray(array $data): CartPosition
    {
        if (!\array_key_exists('name', $data)
            || !\array_key_exists('number', $data)
            || !\array_key_exists('quantity', $data)
            || !\array_key_exists('itemPrice', $data)
            || !\array_key_exists('sum', $data)
        ) {
            throw new \Exception('Not enough arguments to create CartPosition from array.');
        }

        $data['itemPrice'] = self::toFloat($data['itemPrice']);
        $data['sum'] = self::toFloat($data['sum']);

        return new self(
            trim($data['name']),
            trim($data['number']),
            (int) trim($data['quantity']),
            $data['itemPrice'],
            $data['sum']
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getItemPrice(): float
    {
        return $this->itemPrice;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * Convert a given string value to a float
     *
     * @return float
     */
    private static function toFloat($string): float
    {
        if (\is_float($string)) {
            return $string;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $string);
        preg_match('/([0-9]+[\\.]?[0-9]*)/', $float, $matches);

        return (float) ($matches[0] ?? 0.0);
    }
}
