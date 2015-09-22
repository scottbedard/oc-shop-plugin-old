<?php namespace Bedard\Shop\Models;

use Model;
use Exception;

class ShippingSettings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_shipping';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation
     */
    public $rules = [];

    /**
     * Returns all installed shipping calculators
     *
     * @return  array
     */
    public function getCalculatorOptions()
    {
        return Driver::isShipping()->isConfigured()->orderBy('name')->lists('name', 'class');
    }

    /**
     * Determines if a response is required from the shipping calculator
     *
     * @return  boolean
     */
    public static function getIsRequired()
    {
        return (bool) self::get('is_required', false);
    }

    /**
     * Returns the shipping calculator driver
     *
     * @param   Cart|null   $cart
     * @return  mixed
     */
    public static function getCalculator(Cart $cart = null)
    {
        if (!$class = self::get('calculator', false)) {
            return false;
        }

        $shippingInterface = 'Bedard\Shop\Interfaces\ShippingInterface';
        if (!in_array($shippingInterface, class_implements($class))) {
            throw new Exception("Shipping calculators must implement $shippingInterface.");
        }

        if (is_null($cart)) {
            return $class;
        }

        $calculator = new $class;
        if ($driver = Driver::where('class', $class)->first()) {
            $calculator->setDriver($driver);
        }

        $calculator->setCart($cart);

        return $calculator;
    }

}
