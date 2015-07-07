<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Models\Cart;

interface DriverInterface {

    /**
     * Get a value from the driver configuration
     *
     * @param   string      $key
     * @return  mixed|null
     */
    public function getConfig($key);

    /**
     * Set the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart);

    /**
     * Set the driver configuration
     *
     * @param   array   $config
     */
    public function setConfig($config);

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
