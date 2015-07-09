<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Classes\PaymentException;
use Bedard\Shop\Interfaces\PaymentInterface;
use Bedard\Shop\Models\Currency;
use Bedard\Shop\Models\PaymentSettings;
use Cms\Classes\MediaLibrary;
use Exception;
use Omnipay\Omnipay;
use Redirect;
use URL;

class PaypalExpress extends PaymentBase implements PaymentInterface {
    use \Bedard\Shop\Traits\OmnipayGatewayTrait;

    /**
     * Validation rules
     */
    public $rules = [
        'api_username'  => 'required',
        'api_password'  => 'required',
        'api_signature' => 'required',
        'server'        => 'required',
        'border_color'  => 'regex:/^#([A-Fa-f0-9]{6})$/',
    ];

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
                'type'      => 'api-password',
                'required'  => true,
                'span'      => 'right',
            ],
            'api_signature' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_connection',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.api_signature',
                'type'      => 'api-password',
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
            'brand_name' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_appearance',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.brand_name',
            ],
            'logo' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_appearance',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.logo',
                'comment'   => 'bedard.shop::lang.drivers.paypalexpress.logo_comment',
                'type'      => 'mediafinder',
                'mode'      => 'image',
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
    public function executePayment()
    {
        $gateway = $this->openGateway();

        try {

            $total = number_format($this->cart->total, 2, '.', '');

            $payment = $gateway->purchase([
                    'returnUrl' => $this->getResponseURL('success'),
                    'cancelUrl' => $this->getResponseURL('canceled'),
                    'amount'    => $total,
                    'currency'  => Currency::getCode(),
                ])
                ->setShippingAmount($this->cart->shipping_cost)
                ->setItems($this->getItems())
                ->setAmount($total)
                ->setCard($this->getCard())
                ->setTaxAmount(0)
                ->setAddressOverride(1)
                ->setNoShipping(2);

            if ($brand = $this->getConfig('brand_name')) {
                $payment->setBrandName($brand);
            }

            if ($color = $this->getConfig('border_color')) {
                $payment->setBorderColor(substr($color, 1));
            }

            if ($logo = $this->getConfig('logo')) {
                $payment->setLogoImageUrl(URL::to(MediaLibrary::instance()->url($logo)));
            }

            $response = $payment->send();

            // Paypal Express should always return a redirect
            if (!$response->isRedirect()) {
                throw new PaymentException($response->getMessage());
            }

            $this->beginPaymentProcessor();
            return Redirect::to($response->getRedirectUrl());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
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
