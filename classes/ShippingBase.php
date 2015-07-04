<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

class ShippingBase {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Sets the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
    }

}
