<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Models\Cart;

interface DriverInterface {

    /**
     * Set the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart);

    /**
     * Register driver settings
     *
     * @return  array
     */
    public function registerSettings();

    /**
     * Register driver validation
     *
     * @return  array
     */
    public function registerValidation();

}
