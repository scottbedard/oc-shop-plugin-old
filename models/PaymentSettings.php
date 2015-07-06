<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Driver;
use Model;

class PaymentSettings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_payment_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation
     */
    public $rules = [];

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
