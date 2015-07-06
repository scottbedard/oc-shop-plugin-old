<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Driver;
use Model;

class PaymentSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_payment_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Return all installed payment gateways
     *
     * @return  array
     */
    public function getGatewayOptions()
    {
        return Driver::isPayment()->orderBy('name')->lists('name', 'class');
    }
}
