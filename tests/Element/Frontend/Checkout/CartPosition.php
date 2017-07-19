<?php

namespace Shopware\Element\Frontend\Checkout;

class CartPosition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $number;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $itemPrice;

    /**
     * @var float
     */
    private $sum;

    /**
     * @param string $name
     * @param string $number
     * @param int $quantity
     * @param float $itemPrice
     * @param float $sum
     */
    private function __construct($name, $number, $quantity, $itemPrice, $sum)
    {
        $this->name = $name;
        $this->number = $number;
        $this->quantity = $quantity;
        $this->itemPrice = $itemPrice;
        $this->sum = $sum;
    }

    /**
     * @param array $data
     * @return CartPosition
     * @throws \Exception
     */
    public static function fromArray(array $data)
    {
        if (!array_key_exists('name', $data) ||
            !array_key_exists('number', $data) ||
            !array_key_exists('quantity', $data) ||
            !array_key_exists('itemPrice', $data) ||
            !array_key_exists('sum', $data)
        ) {
            throw new \Exception('Not enough arguments to create CartPosition from array.');
        }

        $data['itemPrice'] = self::toFloat($data['itemPrice']);
        $data['sum'] = self::toFloat($data['sum']);

        return new self(
            $data['name'],
            $data['number'],
            $data['quantity'],
            $data['itemPrice'],
            $data['sum']
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Convert a given string value to a float
     *
     * @param $string
     * @return float
     */
    private static function toFloat($string)
    {
        if (is_float($string)) {
            return $string;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $string);
        preg_match("/([0-9]+[\\.]?[0-9]*)/", $float, $matches);

        return floatval($matches[0]);
    }
}