<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Exception;
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

    /**
     * Returns the payment gateway driver
     *
     * @param   Cart|null   $cart
     * @return  mixed
     */
    public static function getGateway(Cart $cart = null)
    {
        if (!$class = self::get('gateway', false)) {
            return false;
        }

        $paymentInterface = 'Bedard\Shop\Interfaces\PaymentInterface';
        if (!in_array($paymentInterface, class_implements($class))) {
            throw new Exception("Payment gateways must implement $paymentInterface.");
        }

        if (is_null($cart)) {
            return $class;
        }

        $driver = Driver::isPayment()->where('class', $class)->first();

        $gateway = new $class;
        $gateway->setCart($cart);
        $gateway->setConfig($driver->config);
        return $gateway;
    }
}
