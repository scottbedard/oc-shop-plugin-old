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
        $backend = Settings::get('backend');
        return isset($backend['editor'])
            ? $backend['editor']
            : 'richeditor';
    }
}
