<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

interface ShippingInterface {

    /**
     * Sets the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function setCart(Cart $cart);

    /**
     * Calculate and save the shipping rates
     */
    public function getRates();

}
