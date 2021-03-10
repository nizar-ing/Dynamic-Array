<?php

namespace App;

class NumberHelper
{

    public static function price(float $price, string $sigle = '€'): string
    {
        return number_format($price, 0, '', ' ') . " $sigle";
    }
}
