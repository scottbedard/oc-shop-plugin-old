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

    /**
     * Returns a route that handles a payment response
     *
     * @param   string      $type
     * @return  string
     */
    public function getResponseURL($type)
    {
        return route('bedard.shop.payments', [
            'cart'      => $this->cart->id,
            'driver'    => $this->driver->id,
            'hash'      => $this->cart->hash,
            'status'    => $type,
        ]);
    }

}
