<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Exception;
use Model;

class ShippingSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bedard_shop_shipping';

    public $settingsFields = 'fields.yaml';

    /**
     * Returns all installed shipping calculators
     *
     * @return  array
     */
    public function getCalculatorOptions()
    {
        return Driver::isShipping()->orderBy('name')->lists('name', 'class');
    }

    /**
     * Returns the shipping calculator behavior
     *
     * @return  string
     */
    public static function getBehavior()
    {
        return self::get('behavior', 'off');
    }

    /**
     * Instantiates the default shipping calculator
     *
     * @param   Cart        $cart
     * @return  mixed
     */
    public static function getCalculator(Cart $cart)
    {
        if (self::getBehavior() == 'off' || (!$calculator = self::get('calculator', false))) {
            return false;
        }

        $shippingInterface = 'Bedard\Shop\Interfaces\ShippingInterface';
        if (!in_array($shippingInterface, class_implements($calculator))) {
            throw new Exception("Shipping calculators must implement $shippingInterface.");
        }

        $class = new $calculator;
        $class->setCart($cart);
        return $class;
    }

}
