<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\DriverBase;
use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Models\Status;

class PaymentBase extends DriverBase {

    /**
     * Abandon the payment process
     *
     * @param   Status  $status     The abandoned payment status
     */
    public function abandonPaymentProcess(Status $status = null)
    {
        $processor = new PaymentProcessor($this->cart, $this->driver);
        $processor->abandon($status);
    }

    /**
     * Begin the payment process
     */
    public function beginPaymentProcess()
    {
        $processor = new PaymentProcessor($this->cart, $this->driver);
        $processor->begin();
    }

    /**
     * Cancel the payment process
     */
    public function cancelPaymentProcess()
    {
        $processor = new PaymentProcessor($this->cart, $this->driver);
        $processor->cancel();
    }

    /**
     * Complete the payment process
     */
    public function completePaymentProcess()
    {
        $processor = new PaymentProcessor($this->cart, $this->driver);
        $processor->complete();
    }

    /**
     * Something went wrong during payment
     */
    public function errorPaymentProcess()
    {
        $processor = new PaymentProcessor($this->cart, $this->driver);
        $processor->error();
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
