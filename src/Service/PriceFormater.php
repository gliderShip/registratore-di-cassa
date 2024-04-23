<?php

namespace App\Service;

use Symfony\Component\Intl\Currencies;

class PriceFormater
{
    public static function format(float $amountInCent, string $currency = 'EUR'): string
    {
        if (!Currencies::exists($currency)) {
            throw new \InvalidArgumentException('Invalid currency ->: ' . $currency);
        }

        $formater = new \NumberFormatter('it', \NumberFormatter::CURRENCY);
        $formater->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);
        return $formater->formatCurrency($amountInCent / 100, $currency);
    }
}
