<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Driver;
use Model;

class ShippingSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_shipping';

    public $settingsFields = 'fields.yaml';

    /**
     * Returns all installed shipping calculators
     *
     * @return  array
     */
    public function getCalculatorOptions()
    {
        return Driver::isShipping()->lists('name', 'class');
    }

    /**
     * Returns the shipping calculator behavior
     *
     * @return  string
     */
    public static function getBehavior()
    {
        return ShippingSettings::get('behavior', 'off');
    }

    /**
     * Returns the selected shipping calculator class
     *
     * @return  string|false
     */
    public static function getCalculatorClass()
    {
        return ShippingSettings::get('calculator', false);
    }

}
