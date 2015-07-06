<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Address;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Customer;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use Bedard\Shop\Models\Shipping;
use Exception;
use October\Rain\Exception\AjaxException;

use Bedard\Shop\Classes\CartSession;

class CartManager extends CartSession {

    /*
     * Summary of error responses
     *
     * CART_INVALID         The cart was not found, or could not be loaded
     * PROMOTION_INVALID    A code was entered, but no running promotion could be found
     */

    /**
     * @var boolean     Helpers to keep track of lazy loading
     */
    protected $itemsLoaded      = false;
    protected $itemDataLoaded   = false;

    /**
     * Loads the cart items relationship
     *
     * @param   boolean     $force
     */
    public function loadItems($force = false)
    {
        if ((!$this->itemsLoaded && $this->cart) || $force) {
            $this->cart->load('items');
            $this->itemsLoaded = true;
        }
    }

    /**
     * Loads the relationships under the CartItem models
     *
     * @param   boolean     $force
     */
    public function loadItemData($force = false)
    {
        if (!$this->cart) return;

        $this->loadItems($force);
        if (!$this->itemDataLoaded || $force) {
            $this->cart->items->load([
                'inventory.product' => function($product) {
                    $product->joinPrices();
                },
                'inventory.values.option',
            ]);

            if ($this->cart->hasPromotion) {
                $this->cart->load('promotion.products');
            }

            $this->itemDataLoaded = true;
        }
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
        $this->loadCart();

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
        $this->loadCart();

        if (!$promotion = Promotion::isRunning()->where('code', $code)->first())
            throw new AjaxException('PROMOTION_INVALID');

        $this->cart->promotion_id = $promotion->id;
        $this->cart->save();
    }

    /**
     * Determines if shipping needs to be calculated, and if so passes the
     * request to the shipping calculator.
     */
    public function calculateShipping()
    {
        if (!$this->cart || !$this->cart->shippingIsRequired) {
            return;
        }

        $this->loadItemData();
        $calculator = Shipping::getCalculator($this->cart);

        if (!$this->cart->shipping_rates = $calculator->getRates()) {
            $this->cart->shipping_failed = true;
        }

        $this->cart->save();
    }

    /**
     * Deletes all items in the cart
     */
    public function clear()
    {
        $this->loadCart();

        CartItem::where('cart_id', $this->cart->id)->delete();
        $this->cart->touch();
    }

    /**
     * Returns a collection of CartItem models, or an empty array
     * if no cart exists.
     *
     * @return  array | Illuminate\Database\Eloquent\Collection
     */
    public function getItems()
    {
        if (!$this->cart) return [];

        $this->loadItemData();
        return $this->cart->items;
    }

    /**
     * Returns the sum of CartItem quantities
     *
     * @return  integer
     */
    public function getItemCount()
    {
        if (!$this->cart) return 0;

        $this->loadItems();
        return $this->cart->items->sum('quantity');
    }

    /**
     * Removes one or more items from the cart
     *
     * @param   integer | array     $itemIds
     */
    public function remove($itemIds)
    {
        $this->loadCart();

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
     * Removes an Address from the cart
     */
    public function removeAddress()
    {
        $this->loadCart();
        $this->cart->address = null;
        $this->cart->save();
    }

    /**
     * Removes a Customer from the cart
     */
    public function removeCustomer()
    {
        $this->loadCart();
        $this->cart->customer_id = null;
        $this->cart->save();
    }

    /**
     * Removes a promotion
     */
    public function removePromotion()
    {
        $this->loadCart();
        $this->cart->promotion_id = null;
        $this->cart->save();
    }

    /**
     * Attaches a customer and address to the cart
     *
     * @param   array   $customerData
     * @param   array   $addressData
     */
    public function setCustomerAddress($customerData, $addressData)
    {
        $this->loadCart();

        try {
            $save = false;
            if (is_array($customerData) && array_filter($customerData) && ($customer = Customer::firstOrCreate($customerData))) {
                $this->cart->customer_id = $customer->id;
                $save = true;
            }

            if (is_array($addressData) && array_filter($addressData) && ($address = Address::firstOrCreate($addressData))) {
                $this->cart->address_id = $address->id;
                $save = true;
            }

            if ($save) {
                $this->cart->save();
            }
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }
    }

    /**
     * Updates the cart
     *
     * @param  array  $items
     */
    public function update($items = [])
    {
        $this->loadCart();

        // Determine if anything has actually changed
        $this->loadItems();
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
