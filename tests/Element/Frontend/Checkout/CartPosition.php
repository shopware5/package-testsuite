<?php

declare(strict_types=1);

namespace Shopware\Element\Frontend\Checkout;

use Exception;

class CartPosition
{
    private string $name;

    private string $number;

    private int $quantity;

    private float $itemPrice;

    private float $sum;

    private function __construct(string $name, string $number, int $quantity, float $itemPrice, float $sum)
    {
        $this->name = $name;
        $this->number = $number;
        $this->quantity = $quantity;
        $this->itemPrice = $itemPrice;
        $this->sum = $sum;
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $data): CartPosition
    {
        if (!\array_key_exists('name', $data)
            || !\array_key_exists('number', $data)
            || !\array_key_exists('quantity', $data)
            || !\array_key_exists('itemPrice', $data)
            || !\array_key_exists('sum', $data)
        ) {
            throw new Exception('Not enough arguments to create CartPosition from array.');
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getItemPrice(): float
    {
        return $this->itemPrice;
    }

    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * Convert a given string value to a float
     *
     * @param string|float $string
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
