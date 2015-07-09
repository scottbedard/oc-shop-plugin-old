<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Interfaces\PaymentInterface;
use Redirect;

class NoPayment extends PaymentBase implements PaymentInterface {

    /**
     * Register configuration fields
     *
     * @return  array
     */
    public function registerFields()
    {
        return [
            'redirect_message' => [
                'label'     => 'bedard.shop::lang.drivers.nopayment.name',
                'type'      => 'partial',
                'path'      => '$/bedard/shop/drivers/payment/nopayment/_message.htm',
            ],
        ];
    }

    /**
     * Begin the payment process
     */
    public function executePayment()
    {
        $this->beginPaymentProcessor();
        return Redirect::to($this->getResponseURL('success'));
    }
}
