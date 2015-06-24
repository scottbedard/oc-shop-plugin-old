<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\CartException;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Product;
use Session;

class CartManager {

    /**
     * @var string      The session key
     */
    const SESSION_KEY = 'bedard.shop.cart';

    /**
     * @var Cart        Cart model
     */
    public $cart;

    /**
     * Initialize the cart session if there is one
     */
    public function __construct()
    {
        if ($session = Session::get(self::SESSION_KEY)) {
            $this->cart = Cart::where('key', $session['key'])
                ->find($session['id']);
        }
    }

    /**
     * Finds or creates a CartItem, and passes to updateQuantity()
     *
     * @param   integer     $productId      The product being added
     * @param   array       $valueIds       Collection of values to identify the inventory
     * @param   integer     $quantity       The number of items to add to the cart item
     */
    public function add($productId, $valueIds, $quantity = 1)
    {
        // Create the cart if it isn't already loaded
        if (!$this->cart) $this->createCart();

        // Select the product being added
        if (!$product = Product::isActive()->find($productId)) {
            throw new CartException('The product was not found or is not active.');
        }

        // Select the inventory being added
        $valueIds = $valueIds ?: [];
        $query = Inventory::where('product_id', $product->id);
        foreach ($valueIds as $valueId) {
            $query->whereHas('values', function($value) use ($valueId) {
                $value->where('id', $valueId);
            });
        }
        $query->has('values', '=', count($valueIds));

        if (!$inventory = $query->first()) {
            throw new CartException('The inventory was not found.');
        }

        // Create and update the cart item
        $cartItem = CartItem::firstOrNew([
            'cart_id'       => $this->cart->id,
            'product_id'    => $product->id,
            'inventory_id'  => $inventory->id,
        ]);

        $quantity += $cartItem->quantity;
        $this->updateQuantity($cartItem, $quantity, $inventory);
    }

    /**
     * Create a new cart session
     */
    protected function createCart()
    {
        $this->cart = Cart::create([
            'key' => str_random(40),
        ]);

        Session::put(self::SESSION_KEY, [
            'id'    => $this->cart->id,
            'key'   => $this->cart->key,
        ]);
    }

    /**
     * Counts the items in the cart
     *
     * @return  integer
     */
    public function itemCount()
    {
        return $this->cart
            ? $this->cart->items->sum('quantity')
            : 0;
    }

    /**
     * Returns the items in the cart
     *
     * @return  Collection | boolean (false)
     */
    public function getItems()
    {
        return $this->cart
            ? $this->cart->items
            : false;
    }

    /**
     * Updates the quantity of an item
     *
     * @param   CartItem    $item       The item being updated
     * @param   integer     $quantity   The new quantity of the item
     * @param   Inventory   $inventory  The inventory of the item (if it was already queried)
     */
    public function updateQuantity(CartItem $item, $quantity, $inventory = false)
    {
        if (!$inventory) {
            $inventory = $item->inventory;
        }

        if ($quantity < 0) {
            $quantity = 0;
        }

        if ($quantity > $inventory->quantity) {
            $quantity = $inventory->quantity;
        }

        $item->quantity = $quantity;
        $item->save();
    }
}
