<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Classes\PaymentException;
use Bedard\Shop\Interfaces\PaymentInterface;
use Bedard\Shop\Models\Currency;
use Exception;
use Omnipay\Omnipay;
use Redirect;

class PaypalExpress extends PaymentBase implements PaymentInterface {
    use \Bedard\Shop\Traits\OmnipayGatewayTrait;

    /**
     * Validation rules
     */
    public $rules = [
        'brand_name'    => 'required',
        'api_username'  => 'required',
        'api_password'  => 'required',
        'api_signature' => 'required',
        'server'        => 'required',
        'border_color'  => 'regex:/^#([A-Fa-f0-9]{6})$/',
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
                'required'  => true,
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
                'required'  => true,
                'span'      => 'left',
            ],
            'api_password' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_password',
                'type'      => 'owl-password',
                'required'  => true,
                'span'      => 'right',
            ],
            'api_signature' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_signature',
                'type'      => 'owl-password',
                'required'  => true,
                'span'      => 'left',
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
                'required'  => true,
                'span'      => 'right',
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
        ];
    }

    /**
     * Begin the payment process
     */
    public function beginPayment()
    {
        $gateway = $this->openGateway();

        try {

            $hash = str_random(40);

            $total = number_format($this->cart->total, 2, '.', '');

            $payment = $gateway->purchase([
                    'returnUrl' => 'http://shop.october.dev/success',
                    'cancelUrl' => 'http://shop.october.dev/cancel',
                    'amount'    => $total,
                    'currency'  => Currency::getCode(),
                ])
                ->setBrandName($this->getConfig('brand_name'))
                ->setItems($this->getItems())
                ->setAmount($total)
                ->setCard($this->getCard())
                ->setTaxAmount(0)
                ->setAddressOverride(1)
                ->setNoShipping(2);

            if ($color = $this->getConfig('border_color')) {
                $payment->setBorderColor('#'.$color[1].$color[3].$color[5]);
            }


            $response = $payment->send();

            // print_r ($this->getConfig('border_color'));

            // Paypal Express should always return a redirect
            if (!$response->isRedirect()) {
                throw new PaymentException($response->getMessage());
            }

            return Redirect::to($response->getRedirectUrl());

            // $response = $gateway->purchase([
            //         'returnUrl' => route('bedard.shop.payment', ['id' => $id, 'hash' => $hash, 'status' => 'success']),
            //         'cancelUrl' => route('bedard.shop.payment', ['id' => $id, 'hash' => $hash, 'status' => 'canceled']),
            //         'amount'    => $total,
            //         'currency'  => Currency::getCode(),
            //     ])
            //     ->setItems($this->getItems())
            //     ->setAmount($total)
            //     ->setShippingAmount($shipping)
            //     ->setCard($this->getCard())
            //     ->setAddressOverride(1)
            //     ->setNoShipping(2)
            //     ->setTaxAmount($tax)
            //     // ->setHeaderImageUrl()
            //     ->setLogoImageUrl($logoUrl)
            //     ->setBrandName($this->config['brand_name'])
            //     ->setBorderColor(str_replace('#', '', $this->config['border_color']))
            //     ->send();

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }

    /**
     * Open an the Paypal Express gateway
     */
    protected function openGateway()
    {
        $username   = $this->getConfig('api_username');
        $password   = $this->getConfig('api_password');
        $signature  = $this->getConfig('api_signature');
        $server     = $this->getConfig('server');

        if (!$username || !$password || !$signature || !$server) {
            throw new PaymentException('Required API credentials are not defined.');
        }

        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setUsername($username);
        $gateway->setPassword($password);
        $gateway->setSignature($signature);

        if ($server == 'sandbox') {
            $gateway->setTestMode(true);
        }

        return $gateway;
    }
}
