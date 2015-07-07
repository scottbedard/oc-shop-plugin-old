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
                'type'      => 'partial',
                'path'      => '$/bedard/shop/drivers/payment/_soon.htm',
            ],
        ];
    }

}
