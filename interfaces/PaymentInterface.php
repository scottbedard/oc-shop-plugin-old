<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Interfaces\DriverInterface;

interface PaymentInterface extends DriverInterface {

    /**
     * Begin the payment process
     */
    public function beginPayment();
}
