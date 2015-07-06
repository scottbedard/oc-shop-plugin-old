<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

class DriverBase {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Set the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Register driver settings
     *
     * @return  array
     */
    public function registerSettings()
    {
        return [];
    }

    /**
     * Register driver validation
     *
     * @return  array
     */
    public function registerValidation()
    {
        return [];
    }

}
