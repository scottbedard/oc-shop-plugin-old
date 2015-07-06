<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

class DriverBase {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Inject the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

}
