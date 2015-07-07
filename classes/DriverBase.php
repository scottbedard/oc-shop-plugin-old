<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

class DriverBase {

    /**
     * @var Cart        The user's shopping cart
     */
    protected $cart;

    /**
     * @var array       Validation rules for driver configuration
     */
    public $rules = [];

    /**
     * @var array       Custom validation error messages
     */
    public $customMessages = [];

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
