<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use October\Rain\Exception\AjaxException;
use Session;

class CartManager {

    /**
     * @var string      The session key
     */
    const SESSION_KEY = 'bedard.shop.cart';

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
        $cartSession = new self();

        if ($session = Session::get(self::SESSION_KEY)) {
            $cartSession->cart = Cart::where('key', $session['key'])
                ->find($session['id']);
                // todo: make sure cart is open
        }

        return $cartSession;
    }

    /**
     * Instantiates a new CartManager, and opens the existing cart,
     * or creates a new one of none exists.
     *
     * @return  CartManager
     */
    public static function openOrCreate()
    {
        $cartSession = self::open();

        if (!$cartSession->cart) {
            $newCart = Cart::create(['key' => str_random(40)]);
            Session::put(self::SESSION_KEY, [
                'id' => $newCart->id,
                'key' => $newCart->key
            ]);

            $cartSession->cart = $newCart;
        }

        return $cartSession;
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
        if (!$this->cart)
            throw new AjaxException('The cart is not loaded.', 1);

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
     * Applies a promotion code
     *
     * @param   string  $code
     */
    public function applyPromotion($code)
    {
        if (!$this->cart)
            throw new AjaxException('The cart is not loaded.', 1);

        if (!$promotion = Promotion::isRunning()->where('code', $code)->first())
            throw new AjaxException('Invalid or expired promotion code.', 4);

        $this->cart->promotion_id = $promotion->id;
        $this->cart->save();
    }

    /**
     * Removes one or more items from the cart
     *
     * @param   integer|array   $itemIds
     */
    public function remove($itemIds)
    {
        if (!$this->cart)
            throw new AjaxException('The cart is not loaded.', 1);

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
     * Updates an item in the cart
     *
     * @param   array   $items
     */
    public function update($items = [])
    {
        if (!$this->cart)
            throw new AjaxException('The cart is not loaded.', 1);

        $this->cart->load('items.inventory');
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
