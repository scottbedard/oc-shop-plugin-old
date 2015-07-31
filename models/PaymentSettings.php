<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\Status;
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
    public $rules = [
        'success_url'   => 'required',
        'canceled_url'  => 'required',
        'error_url'     => 'required',
    ];

    /**
     * Return all installed payment gateways
     *
     * @return  array
     */
    public function getGatewayOptions()
    {
        return Driver::isPayment()->isConfigured()->orderBy('name')->lists('name', 'class');
    }

    public function getAbandonedStatusOptions()
    {
        return Status::where('is_pending', false)->lists('name', 'id');
    }

    /**
     * Get the amount of time before an Order is considered abandoned
     *
     * @return  integer
     */
    public static function getAbandoned()
    {
        return self::get('abandoned', 60);
    }

    /**
     * Return the status to use for abandoned payments
     *
     * @return  integer
     */
    public static function getAbandonedStatus()
    {
        return self::get('abandoned_status');
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

        $gateway = new $class;

        if ($driver = Driver::isPayment()->where('class', $class)->first()) {
            $gateway->setDriver($driver);
        }
        // todo: possibly throw an exception here

        $gateway->setCart($cart);
        return $gateway;
    }

    /**
     * Returns the success URL
     *
     * @return  string
     */
    public static function getSuccessUrl()
    {
        return self::get('success_url');
    }

    /**
     * Returns the canceled URL
     *
     * @return  string
     */
    public static function getCanceledUrl()
    {
        return self::get('canceled_url');
    }

    /**
     * Returns the error URL
     *
     * @return  string
     */
    public static function getErrorUrl()
    {
        return self::get('error_url');
    }
}
