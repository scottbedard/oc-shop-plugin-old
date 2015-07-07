<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

class DriverBase {

    /**
     * @var Cart        The user's shopping cart
     */
    protected $cart;

    /**
     * @var array       The driver configuration
     */
    protected $config = [];

    /**
     * @var array       Validation rules for driver configuration
     */
    public $rules = [];

    /**
     * @var array       Custom validation error messages
     */
    public $customMessages = [];

    /**
     * Get a value from the driver configuration
     *
     * @param   string      $key
     * @return  mixed|null
     */
    public function getConfig($key)
    {
        return isset($this->config[$key])
            ? $this->config[$key]
            : null;
    }

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
     * Set the driver configuration
     *
     * @param   array|null  $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Register configuration fields
     *
     * @return  array
     */
    public function registerFields()
    {
        return [];
    }

    /**
     * Registers tabbed configuration fields
     *
     * @return array
     */
    public function registerTabFields()
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

    /**
     * Run custom messages through the Transliterator
     *
     * @return  array
     */
    public function getCustomMessages()
    {
        return array_map('trans', $this->customMessages);
    }

}
