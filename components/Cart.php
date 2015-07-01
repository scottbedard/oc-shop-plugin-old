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
        $this->itemCount    = $this->cart->getItemCount();
        $this->isEmpty      = $this->itemCount == 0;
    }

    /**
     * Returns the cart items
     *
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public function items()
    {
        return $this->cart->getItems();
    }

    /**
     * Returns the sum of item prices, not taking promotions into consideration
     *
     * @return  float
     */
    public function subtotal()
    {
        return $this->cart->getSubtotal();
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

    /**
     * Removes an item from the cart
     */
    public function onRemoveFromCart()
    {
        try {
            $this->cart->remove(input('item'));
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }

        $this->prepareVars();
    }

    /**
     * Updates items in the cart, and applies a promotion code if submitted
     */
    public function onUpdateCart()
    {
        try {
            $quantities = array_map('intval', input('quantity') ?: []);
            $promotion  = input('promotion') ?: false;

            $this->cart->update($quantities, $promotion);
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }

        $this->prepareVars();
    }

}
