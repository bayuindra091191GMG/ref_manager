<?php

namespace App\Libs;

use App\Models\Configuration;

class TaxUtilities
{
    private const DEFAULT_PPN_PERCENTAGE = 10.0;

    public static function getPpnPercentage(): float
    {
        $value = Configuration::where('key', 'ppn')->value('value');

        if (! is_numeric($value)) {
            return self::DEFAULT_PPN_PERCENTAGE;
        }

        $percentage = (float) $value;

        return $percentage >= 0 ? $percentage : self::DEFAULT_PPN_PERCENTAGE;
    }

    public static function calculatePpnValue(float $subtotal, ?float $percentage = null): float
    {
        $ppnPercentage = $percentage ?? self::getPpnPercentage();

        return $subtotal * ($ppnPercentage / 100);
    }

    public static function calculateTotalWithPpn(float $subtotal, ?float $percentage = null): float
    {
        return ceil($subtotal + self::calculatePpnValue($subtotal, $percentage));
    }
}
