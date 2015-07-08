<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Interfaces\PaymentInterface;

class PaypalExpress extends PaymentBase implements PaymentInterface {

    /**
     * Validation rules
     */
    public $rules = [
        'brand_name'    => 'required',
        'api_username'  => 'required',
        'api_password'  => 'required',
        'api_signature' => 'required',
        'is_live'       => 'boolean',
    ];

    /**
     * Register configuration fields
     *
     * @return  array
     */
    public function registerFields()
    {
        return [
            'brand_name' => [
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.brand_name',
            ],
        ];
    }

    /**
     * Register tabbed configuration fields
     *
     * @return  array
     */
    public function registerTabFields()
    {
        return [
            'api_username' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_username',
                'span'      => 'left',
            ],
            'api_password' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_password',
                'span'      => 'right',
            ],
            'api_signature' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_signature',
                'span'      => 'left',
            ],
            'logo_url' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_appearance',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.logo_url',
            ],
            'border_color' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_appearance',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.border_color',
                'type'      => 'colorpicker',
            ],
            'server' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.server',
                'type'      => 'dropdown',
                'options'   => [
                    'sandbox' => 'bedard.shop::lang.drivers.paypalexpress.sandbox',
                    'production' => 'bedard.shop::lang.drivers.paypalexpress.production',
                ],
                'default'   => 'sandbox',
                'span'      => 'right',
            ],
        ];
    }
}
