<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Interfaces\DriverInterface;

interface ShippingInterface extends DriverInterface {

    /**
     * Calculate and save the shipping rates
     */
    public function getRates();

}
