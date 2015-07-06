<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;

interface ShippingInterface {

    /**
     * Inject the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function __construct(Cart $cart);

    /**
     * Calculate and save the shipping rates
     */
    public function getRates();

}
