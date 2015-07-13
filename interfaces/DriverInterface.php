<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\Order;

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
     * @param   Driver  $driver
     */
    public function setDriver(Driver $driver);

    /**
     * Set the Order model
     *
     * @param   Order   $order
     */
    public function setOrder(Order $order);

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
