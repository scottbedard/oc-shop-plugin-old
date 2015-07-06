<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Models\Cart;

interface DriverInterface {

    /**
     * Inject the shopping cart object
     *
     * @param   Cart    $cart
     */
    public function __construct(Cart $cart);

}
