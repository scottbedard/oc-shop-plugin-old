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
     * @return  string
     */
    public static function getEditor()
    {
        return Settings::get('editor', 'richeditor');
    }
}
