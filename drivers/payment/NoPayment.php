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
                'type'      => 'owl-comment',
                'comment'   => 'bedard.shop::lang.drivers.nopayment.message',
            ],
        ];
    }

    /**
     * Begin the payment process
     */
    public function executePayment()
    {
        $this->beginPaymentProcess();
        return Redirect::to($this->getResponseURL('success'));
    }

    /**
     * Since payments through this driver will never be automatically completed,
     * overwrite the abandon method to prevent them from timing out.
     */
    public function abandonPaymentProcess()
    {
    }
}
