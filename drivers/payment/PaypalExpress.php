<?php namespace Bedard\Shop\Drivers\Payment;

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Classes\PaymentBase;
use Bedard\Shop\Classes\PaymentException;
use Bedard\Shop\Interfaces\PaymentInterface;
use Bedard\Shop\Models\Currency;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Status;
use Cms\Classes\MediaLibrary;
use Exception;
use Omnipay\Omnipay;
use Redirect;
use Session;
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
        $statuses = Status::all()->lists('name', 'id');

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
            'behavior_comment' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_behavior',
                'type'      => 'owl-comment',
                'comment'   => 'bedard.shop::lang.drivers.paypalexpress.behavior_comment',
            ],
            'status_begin' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_behavior',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.status_begin',
                'type'      => 'dropdown',
                'options'   => $statuses,
            ],
            'status_cancel' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_behavior',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.status_cancel',
                'type'      => 'dropdown',
                'options'   => $statuses,
            ],
            'status_complete' => [
                'tab'       => 'bedard.shop::lang.drivers.paypalexpress.tab_behavior',
                'label'     => 'bedard.shop::lang.drivers.paypalexpress.status_complete',
                'type'      => 'dropdown',
                'options'   => $statuses,
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

    /**
     * Begin the payment process
     */
    public function executePayment()
    {
        $gateway = $this->openGateway();

        try {

            $shipping   = $this->cart->shipping_cost;
            $items      = $this->getItems();
            $amount     = number_format($this->cart->total, 2, '.', '');

            $params = [
                'returnUrl' => $this->getResponseURL('success'),
                'cancelUrl' => $this->getResponseURL('canceled'),
                'amount'    => $amount,
                'currency'  => Currency::getCode(),
            ];

            Session::put('paypal_express_data', [
                'params'    => $params,
                'shipping'  => $shipping,
                'items'     => $items,
                'amount'    => $amount,
            ]);
        	Session::save();

            $payment = $gateway->purchase($params)
                ->setShippingAmount($shipping)
                ->setItems($items)
                ->setAmount($amount)
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

            // Notify the payment processor that we've started
            $this->beginPaymentProcess();
            return Redirect::to($response->getRedirectUrl());

        } catch (Exception $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * Complete the payment and charge the user
     */
    public function completePaymentProcess()
    {
        $gateway = $this->openGateway();

        $data = Session::pull('paypal_express_data');
        $response = $gateway->completePurchase($data['params'])
            ->setShippingAmount($data['shipping'])
            ->setItems($data['items'])
            ->setAmount($data['amount'])
            ->send();

        $paypalResponse = $response->getData();

        if(isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {
            parent::completePaymentProcess();
        } else {
            // Payment failed
        }
    }
}
