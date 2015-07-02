<?php namespace Bedard\Shop\Components;

use App;
use Bedard\Shop\Models\CartItem;
use Cms\Classes\ComponentBase;

class Cart extends ComponentBase
{

    /**
     * @var CartManager     The cart manager instance
     */
    protected $manager;

    /**
     * @var CartModel       The user's shopping cart
     */
    public $cart;

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

    public function init()
    {
        $this->manager = App::make('Bedard\Shop\Classes\CartManager');
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        // Default cart data
        $this->itemCount    = 0;
        $this->isEmpty      = true;

        // If we have a cart, replace the defaults
        $this->cart = $this->manager->cart;
        if ($this->cart) {
            $this->itemCount    = CartItem::where('cart_id', $this->cart->id)->sum('quantity');
            $this->isEmpty      = $this->itemCount == 0;
        }
    }

    /**
     * Returns all items in the cart, and loads relationships
     *
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public function getItems()
    {
        if (!$this->cart)
            return [];

        $this->cart->loadRelationships();
        return $this->cart->items;
    }

    /**
     * Add an item to the cart
     */
    public function onAddToCart()
    {
        $productId  = intval(input('product'));
        $valueIds   = array_map('intval', input('options') ?: []);
        $quantity   = intval(input('quantity')) ?: 1;

        $this->manager->add($productId, $valueIds, $quantity);
        $this->prepareVars();
    }

    /**
     * Removes all items from the cart
     */
    public function onClearCart()
    {
        $this->manager->clear();
        $this->prepareVars();
    }

    /**
     * Remove items from the cart
     */
    public function onRemoveFromCart()
    {
        $this->manager->remove(intval(input('remove')));
        $this->prepareVars();
    }

    /**
     * Remove a promotion from the cart
     */
    public function onRemovePromotion()
    {
        $this->manager->removePromotion();
        $this->prepareVars();
    }

    /**
     * Update the cart items
     */
    public function onUpdateCart()
    {
        $this->manager->update(array_map('intval', input('items') ?: []));

        if ($promotion = input('promotion'))
            $this->manager->applyPromotion($promotion);

        $this->prepareVars();
    }

}
