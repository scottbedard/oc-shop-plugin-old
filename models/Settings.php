<?php namespace Bedard\Shop\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Return the editor type ("richeditor" or "code")
     *
     * @return  string (default: richeditor)
     */
    public static function getEditor()
    {
        return Settings::get('backend_editor', 'richeditor');
    }

    /**
     * Returns the cart life in minutes, or false if disabled
     *
     * @return  integer|false   (default: 10080)
     */
    public static function getCartLife()
    {
        return Settings::get('cart_life', 10080) ?: false;
    }

    /**
     * Returns the unit of weight
     *
     * @return  string          (default: 'oz')
     */
    public static function getWeightUnits()
    {
        return Settings::get('weight_unit', 'oz');
    }
}
