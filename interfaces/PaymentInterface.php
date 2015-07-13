<?php namespace Bedard\Shop\Interfaces;

use Bedard\Shop\Interfaces\DriverInterface;
use Bedard\Shop\Models\Status;

interface PaymentInterface extends DriverInterface {

    /**
     * Abandon the payment process
     */
    public function abandonPaymentProcess(Status $status = null);

    /**
     * Begin the payment process
     */
    public function beginPaymentProcess();

    /**
     * Cancel the payment process
     */
    public function cancelPaymentProcess();

    /**
     * Complete the payment process
     */
    public function completePaymentProcess();

    /**
     * Something went wrong during payment
     */
    public function errorPaymentProcess();

    /**
     * Begin the payment process
     */
    public function executePayment();
}
