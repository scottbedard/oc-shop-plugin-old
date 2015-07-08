<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;

interface DriverInterface {

    /**
     * Get a value from the driver configuration
     *
     * @param   string      $key
     * @return  mixed|null
     */
    public function getConfig($key);

    /**
     * Set the Cart model
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart);

    /**
     * Set the Driver model and config
     *
     * @param   Driver      $driver
     */
    public function setDriver(Driver $driver);

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

}
