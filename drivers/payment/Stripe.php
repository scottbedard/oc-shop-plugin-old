<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Interfaces\PaymentInterface;

class Stripe extends PaymentBase implements PaymentInterface {

    /**
     * Register configuration fields
     *
     * @return  array
     */
    public function registerFields()
    {
        return [
            'message' => [
                'label'     => 'Stripe',
                'type'      => 'owl-comment',
                'comment'   => 'This payment gateway is expected to be added soon, hang tight until then!',
            ],
        ];
    }

    /**
     * Begin the payment process
     */
    public function executePayment()
    {
        return;
    }
}
