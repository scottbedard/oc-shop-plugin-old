<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Interfaces\PaymentInterface;

class PaypalExpress extends PaymentBase implements PaymentInterface {

    /**
     * Register driver settings
     *
     * @return  array
     */
    public function registerSettings()
    {
        return [
            'paypal_express[brand_name]' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.driver',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.brand_name',
            ],
            'paypal_express[is_live]' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.driver',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.server',
                'type'      => 'radio',
                'options'   => [
                    '1' => 'bedard.shop::lang.drivers.paypalexpress.live',
                    '0' => 'bedard.shop::lang.drivers.paypalexpress.sandbox',
                ],
                'default'   => '0',
                'span'      => 'right',
            ],
            'paypal_express[api_username]' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.driver',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_username',
                'span'      => 'left',
            ],
            'paypal_express[api_password]' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.driver',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_password',
                'span'      => 'left',
            ],
            'paypal_express[api_signature]' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.driver',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_signature',
                'span'      => 'left',
            ],
        ];
    }
}
