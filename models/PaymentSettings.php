<?php namespace Bedard\Shop\Models;

use Model;

class PaymentSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_payment_settings';

    public $settingsFields = 'fields.yaml';
}
