<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Settings;
use Lang;

class WeightHelper {

    /**
     * Converts weight to a specified unit
     *
     * @param   float           $weight     The starting weight
     * @param   string          $to         The desired unit
     * @param   string|false    $from       The starting unit
     */
    public static function convert($weight, $to, $from)
    {
        // The number of grams in a given unit
        $kilogram   = 1000;
        $ounce      = 28.3495;
        $pound      = 453.592;

        // First convert the weight to grams
        if ($from == 'oz') {
            $weight = $weight * $ounce;
        } elseif ($from == 'lb') {
            $weight = $weight * $pound;
        } elseif ($from == 'kg') {
            $weight = $weight * $kilogram;
        }

        // Then convert to the desired unit
        if ($to == 'oz') {
            $weight = $weight / $ounce;
        } elseif ($to == 'lb') {
            $weight = $weight / $pound;
        } elseif ($to == 'kg') {
            $weight = $weight / $kilogram;
        }

        return round($weight, 4);
    }

    /**
     * Inserts the weight unit and type into a language string
     *
     * @param   string      $type
     * @param   boolean     $strtolower
     * @return  strign
     */
    public static function getString($type, $strtolower)
    {
        $unit = Settings::getWeightUnits();
        $translation = Lang::get('bedard.shop::lang.common.weight_'.$unit.'_'.$type);

        return $strtolower
            ? strtolower($translation)
            : $translation;
    }

    /**
     * Returns the abbreviated weight unit
     *
     * @param   boolean     $strtolower
     * @return  string
     */
    public static function getAbbreviation($strtolower = true)
    {
        return self::getString('abbreviated', $strtolower);
    }

    /**
     * Returns the singular weight unit
     *
     * @return  string
     */
    public static function getSingular($strtolower = true)
    {
        return self::getString('singular', $strtolower);
    }

    /**
     * Returns the plural weight unit
     *
     * @return  string
     */
    public static function getPlural($strtolower = true)
    {
        return self::getString('plural', $strtolower);
    }
}
