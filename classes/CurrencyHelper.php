<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Currency;

class CurrencyHelper {

    /**
     * Formats an amount of money
     *
     * @param   float   $amount
     * @return  string
     */
    public static function format($amount = 0)
    {
        if (!is_numeric($amount)) return false;

        $decimal = Currency::getDecimal();
        $thousands = Currency::getThousands();

        $formatted = e(Currency::getSymbol().number_format($amount, 2, $decimal, $thousands));

        return Currency::getHideDoubleZeros()
            ? str_replace('.00', '', $formatted)
            : $formatted;
    }
}
