<?php namespace Bedard\Shop\Components;

use App;
use Cms\Classes\ComponentBase;

class Checkout extends ComponentBase
{

    /**
     * @var Bedard\Shop\Classes\CartManager
     */
    protected $manager;

    /**
     * @var Bedard\Shop\Models\Cart
     */
    public $cart;

    /**
     * Component Details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Checkout Component',
            'description' => 'No description provided yet...'
        ];
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
        $this->cart = $this->manager->cart;
    }

}
