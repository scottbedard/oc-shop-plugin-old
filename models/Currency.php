<?php namespace Bedard\Shop\Models;

use Model;

class Currency extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_currency';

    public $settingsFields = 'fields.yaml';

    /**
     * Returns the currency code being used
     *
     * @return  string | boolean (false)
     */
    public static function getCode()
    {
        return Currency::get('code', false);
    }

    /**
     * Returns the decimal character
     *
     * @return  string
     */
    public static function getDecimal()
    {
        return Currency::get('decimal', '.');
    }

    /**
     * Returns the currency symbol
     *
     * @return  string
     */
    public static function getSymbol()
    {
        return Currency::get('symbol', '');
    }

    /**
     * Returns the thousands seperator
     *
     * @return  string
     */
    public static function getThousands()
    {
        return Currency::get('thousands', '');
    }

    /**
     * Formats an amount of money
     *
     * @param   float   $amount
     * @return  string
     */
    public static function format($amount = 0)
    {
        $decimal = Currency::getDecimal();
        $thousands = Currency::getThousands();

        return is_numeric($amount)
            ? e(Currency::getSymbol().number_format($amount, 2, $decimal, $thousands))
            : false;
    }
}
