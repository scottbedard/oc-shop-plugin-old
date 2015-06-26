<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Classes\CartManager;
use Cms\Classes\ComponentBase;
use Exception;
use October\Rain\Exception\AjaxException;

class Cart extends ComponentBase
{

    /**
     * @var integer     The number of items in the cart
     */
    public $itemCount = 0;

    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'bedard.shop::lang.components.cart.name',
            'description' => 'bedard.shop::lang.components.cart.description',
        ];
    }

    /**
     * Initialize the cart session
     */
    public function init()
    {
        $this->cart = new CartManager;
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    /**
     * Set up component variables
     */
    protected function prepareVars()
    {
        $this->itemCount = $this->cart->itemCount();
    }

    /**
     * Add a product to the cart
     */
    public function onAddToCart()
    {
        try {
            $productId  = intval(input('product'));
            $valueIds   = array_map('intval', input('values') ?: []);
            $quantity   = input('quantity') ? intval(input('quantity')) : 1;

            $this->cart->add($productId, $valueIds, $quantity);
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }

        $this->prepareVars();
    }

}
