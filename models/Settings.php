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
        return self::get('backend_editor', 'richeditor');
    }

    /**
     * Returns the cart life in minutes, or false if disabled
     *
     * @return  integer|false   (default: 10080)
     */
    public static function getCartLife()
    {
        return self::get('cart_life', 10080) ?: false;
    }

    /**
     * Returns the cart validation setting
     *
     * @return  boolean
     */
    public static function getCartValidation()
    {
        return (bool) self::get('cart_validation', true);
    }

    /**
     * Returns the unit of weight
     *
     * @return  string          (default: 'oz')
     */
    public static function getWeightUnits()
    {
        return self::get('weight_unit', 'oz');
    }
}
