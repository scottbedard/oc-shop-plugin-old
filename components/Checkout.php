<?php namespace Bedard\Shop\Components;

use App;
use Cms\Classes\ComponentBase;
use Lang;
use RainLab\Location\Models\Country;

class Checkout extends ComponentBase
{

    /**
     * @var Bedard\Shop\Models\Address
     */
    public $address;

    /**
     * @var Bedard\Shop\Models\Cart
     */
    public $cart;

    /**
     * @var Bedard\Shop\Models\Customer
     */
    public $customer;

    /**
     * @var Bedard\Shop\Classes\CartManager
     */
    protected $manager;

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
        $options[0] = Lang::get('bedard.shop::lang.components.checkout.default_country_none');
        $options = array_merge($options, Country::getNameList());

        return $options;
    }

    public function init()
    {
        $this->manager = App::make('Bedard\Shop\Classes\CartManager');
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    public function prepareVars()
    {
        $this->cart             = $this->manager->cart;
        $this->defaultCountry   = intval($this->property('defaultCountry'));

        if ($this->cart && $this->cart->address_id) {
            $this->address = $this->cart->address;
        }
    }

    /**
     * Attach a customer and/or address to the cart
     */
    public function onSubmitDetails()
    {
        $customer   = input('customer');
        $address    = input('address');

        $this->manager->setCustomerAddress($customer, $address);
    }
}
