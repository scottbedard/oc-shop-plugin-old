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
     * Register configuration fields
     *
     * @return  array
     */
    public function registerFields();

    /**
     * Registers tabbed configuration fields
     *
     * @return array
     */
    public function registerTabFields();

    /**
     * Register configuration validation
     *
     * @return  array
     */
    public function registerValidation();

}
