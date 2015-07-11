<?php namespace Bedard\Shop\Components;

use App;
use Cms\Classes\ComponentBase;
use Exception;
use Lang;
use October\Rain\Exception\AjaxException;
use RainLab\Location\Models\Country;

class Checkout extends ComponentBase
{
    use \Bedard\Shop\Traits\CartAccessTrait;

    /**
     * @var Bedard\Shop\Models\Address
     */
    public $billing_address;

    /**
     * @var Bedard\Shop\Models\Address
     */
    public $shipping_address;

    /**
     * @var Bedard\Shop\Models\Customer
     */
    public $customer;

    /**
     * Component Details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'bedard.shop::lang.components.checkout.name',
            'description' => 'bedard.shop::lang.components.checkout.description'
        ];
    }

    /**
     * Component Properties
     *
     * @return  array
     */
    public function defineProperties()
    {
        return [
            'defaultCountry' => [
                 'title'                => 'bedard.shop::lang.components.checkout.default_country',
                 'description'          => 'bedard.shop::lang.components.checkout.default_country_description',
                 'type'                 => 'dropdown',
                 'showExternalParam'    => false,
            ]
        ];
    }

    /**
     * Returns an array of default country options
     *
     * @return  array
     */
    public function getDefaultCountryOptions()
    {
        $options    = [];
        $options[0] = Lang::get('bedard.shop::lang.components.checkout.default_country_none');
        $options    = array_merge($options, Country::getNameList());

        return $options;
    }

    public function onRun()
    {
        $this->prepareCart();
        $this->prepareVars();
    }

    public function prepareVars()
    {
        if (!$this->cart) {
            return;
        }

        if ($this->cart->hasCustomer) {
            $this->customer = $this->cart->customer;
        }

        if ($this->cart->hasAddresses) {
            $this->shipping_address = $this->cart->shipping_address;
            $this->billing_address = $this->cart->billing_address;
            $this->manager->calculateShipping();
        }

        $this->readyToPay = $this->cart->hasCustomer && $this->cart->hasAddresses && !$this->cart->shippingIsRequired;
    }

    /**
     * Removes an Address from the cart
     */
    public function onRemoveAddress()
    {
        $this->manager->removeAddress();
        $this->prepareCart();
        $this->prepareVars();
    }

    /**
     * Removes a Customer from the cart
     */
    public function onRemoveCustomer()
    {
        $this->manager->removeCustomer();
        $this->prepareCart();
        $this->prepareVars();
    }

    /**
     * Attach a customer and/or address to the cart
     */
    public function onSubmitDetails()
    {
        $customer = input('customer');
        $shipping = input('shipping');

        if (isset($shipping['is_billing'])) {
            unset($shipping['is_billing']);
            $billing = $shipping;
        } else {
            $billing = input('billing');
        }

        $this->manager->setCustomerAddress($customer, $shipping, $billing);
        $this->prepareCart();
        $this->prepareVars();
    }

    /**
     * Pass the request to the payment driver
     */
    public function onBeginPayment()
    {
        try {
            $response = $this->manager->initPayment();
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }

        return $response;
    }
}
