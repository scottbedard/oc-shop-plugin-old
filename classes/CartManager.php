<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use Bedard\Shop\Models\Settings;
use Cookie;
use October\Rain\Exception\AjaxException;
use Request;
use Session;

class CartManager {

    /*
     * Summary of error responses
     *
     * CART_INVALID         The cart was not found, or could not be loaded
     * PROMOTION_INVALID    A code was entered, but no running promotion could be found
     */

    /**
     * @var string      The session key
     */
    const SESSION_KEY = 'bedard_shop_cart';

    /**
     * @var Cart        The user's shopping cart model
     */
    public $cart;

    /**
     * Instantiate a new CartManager, and open the existing cart
     *
     * @return  CartManager
     */
    public static function open()
    {
        $manager = new self();

        // First, attempt to load the cart from the user's session
        if ($session = Session::get(self::SESSION_KEY)) {
            $manager->cart = Cart::where('key', $session['key'])
                ->find($session['id']);
                // todo: make sure cart is open
        }

        // If that fails, check if we have the cart data saved in a cookie
        elseif (Settings::getCartLife() && $cookie = Request::cookie(self::SESSION_KEY)) {
            $manager->cart = Cart::where('key', $cookie['key'])
                ->find($cookie['id']);
                // todo: make sure cart is open
        }

        return $manager;
    }

    /**
     * Instantiates a new CartManager, and opens the existing cart,
     * or creates a new one of none exists.
     *
     * @return  CartManager
     */
    public static function openOrCreate()
    {
        $manager = self::open();

        // Create a new cart
        if (!$manager->cart) {
            $cart = Cart::create(['key' => str_random(40)]);
            Session::put(self::SESSION_KEY, [
                'id'    => $cart->id,
                'key'   => $cart->key
            ]);

            $manager->cart = $cart;
        }

        // Create a cart cookie
        if ($life = Settings::getCartLife()) {
            Cookie::queue(self::SESSION_KEY, [
                'id'    => $manager->cart->id,
                'key'   => $manager->cart->key,
            ], $life);
        }

        return $manager;
    }

    /**
     * Verifies that the cart is open, and throws an exception if it's not
     */
    protected function verifyCart()
    {
        if (!$this->cart)
            throw new AjaxException('CART_INVALID');
    }

    /**
     * Adds a product to the cart
     *
     * @param   integer     $productId
     * @param   array       $valueIds
     * @param   integer     $quantity
     */
    public function add($productId, $valueIds = [], $quantity = 1)
    {
        $this->verifyCart();

        if (!$product = Product::isEnabled()->find($productId))
            throw new AjaxException('The requested product was not found, or is not enabled.', 2);

        if (!$inventory = Inventory::where('product_id', $product->id)->findByValues($valueIds))
            throw new AjaxException('The requested inventory was not found.', 3);

        $cartItem = CartItem::firstOrNew([
            'cart_id'       => $this->cart->id,
            'product_id'    => $product->id,
            'inventory_id'  => $inventory->id,
        ]);

        $cartItem->quantity += $quantity;
        if ($cartItem->quantity > $inventory->quantity)
            $cartItem->quantity = $inventory->quantity;

        $cartItem->save();
        $this->cart->touch();
    }

    /**
     * Applies a promotion
     *
     * @param   string  $code
     */
    public function applyPromotion($code)
    {
        $this->verifyCart();

        if (!$promotion = Promotion::isRunning()->where('code', $code)->first())
            throw new AjaxException('PROMOTION_INVALID');

        $this->cart->promotion_id = $promotion->id;
        $this->cart->save();
    }

    /**
     * Deletes all items in the cart
     */
    public function clear()
    {
        $this->verifyCart();

        CartItem::where('cart_id', $this->cart->id)->delete();
        $this->cart->touch();
    }

    /**
     * Removes one or more items from the cart
     *
     * @param   integer|array   $itemIds
     */
    public function remove($itemIds)
    {
        $this->verifyCart();

        CartItem::where('cart_id', $this->cart->id)
            ->where(function($query) use ($itemIds) {
                if (is_array($itemIds)) {
                    $query->whereIn('id', $itemIds);
                } else {
                    $query->where('id', $itemIds);
                }
            })
            ->delete();

        $this->cart->touch();
    }

    /**
     * Removes a promotion
     */
    public function removePromotion()
    {
        $this->verifyCart();

        $this->cart->promotion_id = null;
        $this->cart->save();
    }

    /**
     * Updates the cart
     *
     * @param  array  $items [description]
     * @return [type]        [description]
     */
    public function update($items = [])
    {
        $this->verifyCart();

        // Determine if anything has actually changed
        $this->cart->load('items');
        $updated = false;
        foreach ($this->cart->items as $cartItem) {
            if (!array_key_exists($cartItem->id, $items))
                continue;
            if ($cartItem->quantity != $items[$cartItem->id])
                $updated = true;
        }

        if (!$updated) return;

        // Update the new values
        $this->cart->items->load('inventory');
        foreach ($this->cart->items as $cartItem) {
            if (!array_key_exists($cartItem->id, $items))
                continue;

            $cartItem->quantity = $items[$cartItem->id];
            if ($cartItem->quantity > $cartItem->inventory->quantity)
                $cartItem->quantity = $cartItem->inventory->quantity;

            $cartItem->save();
        }

        $this->cart->touch();
    }
}
