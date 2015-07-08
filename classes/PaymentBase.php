<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\DriverBase;
use Bedard\Shop\Classes\PaymentProcessor;

class PaymentBase extends DriverBase {

    /**
     * Begin the payment process
     */
    public function beginPaymentProcessor()
    {
        $processor = new PaymentProcessor($this->cart);
        $processor->begin();
    }

}
