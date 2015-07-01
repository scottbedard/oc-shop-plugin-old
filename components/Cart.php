<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Cms\Classes\ComponentBase;

class Cart extends ComponentBase
{

    /**
     * @var CartModel   The user's shopping cart
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

    /**
     * Initialize the user's shopping cart
     */
    public function onRun()
    {
        $manager = CartManager::open();
        $this->prepareVars($manager->cart);
    }

    /**
     * Set up component variables
     *
     * @param   Bedard\Shop\Models\Cart     $cart
     */
    protected function prepareVars($cart)
    {
        // Default cart data
        $this->itemCount    = 0;
        $this->isEmpty      = true;

        // If we have a cart, replace the defaults
        if ($this->cart = $cart) {
            $this->itemCount    = CartItem::where('cart_id', $cart->id)->sum('quantity');
            $this->isEmpty      = $this->itemCount == 0;

            // Reset the cart loaded property
            $this->cart->isLoaded = false;
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

        $manager = CartManager::openOrCreate();
        $manager->add($productId, $valueIds, $quantity);
        $this->prepareVars($manager->cart);
    }

    /**
     * Remove items from the cart
     */
    public function onRemoveFromCart()
    {
        $manager = CartManager::openOrCreate();
        $manager->remove(intval(input('remove')));
        $this->prepareVars($manager->cart);
    }

    /**
     * Remove a promotion from the cart
     */
    public function onRemovePromotion()
    {
        $manager = CartManager::openOrCreate();
        $manager->removePromotion();
        $this->prepareVars($manager->cart);
    }

    /**
     * Update the cart items
     */
    public function onUpdateCart()
    {
        $manager = CartManager::openOrCreate();
        $manager->update(array_map('intval', input('items') ?: []));

        if ($promotion = input('promotion'))
            $manager->applyPromotion($promotion);

        $this->prepareVars($manager->cart);
    }

}
