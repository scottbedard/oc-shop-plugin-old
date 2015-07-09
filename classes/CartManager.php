<?php namespace Bedard\Shop\Classes;

use Carbon\Carbon;
use Exception;
use Bedard\Shop\Classes\CartSession;
use Bedard\Shop\Models\Address;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Customer;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use Bedard\Shop\Models\Settings;
use Bedard\Shop\Models\ShippingSettings;
use October\Rain\Exception\AjaxException;

class CartManager extends CartSession {

    /*
     * Summary of AjaxException messages
     *
     * CART_INVALID             The cart was invalid, and inventory quantities were adjusted
     * CART_NOT_FOUND           The cart was not found, or could not be loaded
     * INVENTORY_NOT_FOUND      An inventory was not found
     * GATEWAY_NOT_FOUND        The payment gateway was not found, or could not be instantiated
     * PRODUCT_NOT_FOUND        A product was not found
     * PROMOTION_NOT_FOUND      A promotion was not found
     */

    /**
     * @var boolean     Helpers to keep track of lazy loading
     */
    protected $itemsLoaded = false;
    protected $itemDataLoaded = false;

    /**
     * @var boolean     Determines if cart was modified due to an invalid quantity
     */
    public $cartWasInvalid = false;

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
        if (!$this->cart) {
            return;
        }

        $this->loadItems($force);
        if (!$this->itemDataLoaded || $force) {
            $this->cart->items->load([
                'inventory.product' => function($product) {
                    $product->joinPrices();
                },
                'inventory.values.option',
            ]);

            if (Settings::getCartValidation()) {
                $this->cartWasInvalid = !$this->cart->validateQuantities();
            }

            if ($this->cart->hasPromotion) {
                $this->cart->load('promotion.products');
            }

            $this->itemDataLoaded = true;
        }
    }

    /**
     * Reset all shipping fields when an action is complete, and update the timestamp
     */
    protected function actionCompleted()
    {
        $this->cart->hash               = str_random(40);
        $this->cart->shipping_rates     = null;
        $this->cart->shipping_id        = null;
        $this->cart->shipping_failed    = false;
        $this->cart->updated_at         = Carbon::now();
        $this->cart->save();
    }

    /**
     * Adds an item to the cart
     *
     * @param   integer     $productId      ID of the product being added.
     * @param   array       $valueIds       Inventory's unique value signature
     * @param   integer     $quantity       The number of items to add
     */
    public function addItem($productId, $valueIds = [], $quantity = 1)
    {
        $this->loadCart();

        if (!$product = Product::isEnabled()->find($productId)) {
            throw new AjaxException('PRODUCT_NOT_FOUND');
        }

        if (!$inventory = Inventory::where('product_id', $product->id)->findByValues($valueIds)) {
            throw new AjaxException('INVENTORY_NOT_FOUND');
        }

        $cartItem = CartItem::firstOrNew([
            'cart_id'       => $this->cart->id,
            'product_id'    => $product->id,
            'inventory_id'  => $inventory->id,
        ]);

        $cartItem->quantity += $quantity;
        if ($cartItem->quantity > $inventory->quantity) {
            $cartItem->quantity = $inventory->quantity;
        }

        $cartItem->save();
        $this->actionCompleted();
    }

    /**
     * Applies a promotion to the cart
     *
     * @param   string  $code
     */
    public function applyPromotion($code)
    {
        $this->loadCart();

        if (!$promotion = Promotion::isRunning()->where('code', $code)->first()) {
            throw new AjaxException('PROMOTION_NOT_FOUND');
        }

        $this->cart->promotion_id = $promotion->id;
        $this->actionCompleted();
    }

    /**
     * Determines if shipping needs to be calculated, and if so passes the
     * request to a shipping calculator.
     */
    public function calculateShipping()
    {
        if (!$this->cart || !$this->cart->shippingIsRequired) {
            return;
        }

        $this->loadItemData();

        $calculator = ShippingSettings::getCalculator($this->cart);

        if (!$this->cart->shipping_rates = $calculator->getRates()) {
            $this->cart->shipping_failed = true;
        }

        $this->cart->save();
    }

    /**
     * Removes all items from the cart
     */
    public function clearItems()
    {
        $this->loadCart();

        CartItem::where('cart_id', $this->cart->id)->delete();

        $this->actionCompleted();
    }

    /**
     * Returns a collection of CartItem models, or an empty array
     * if no cart exists.
     *
     * @return  array|Illuminate\Database\Eloquent\Collection
     */
    public function getItems()
    {
        if (!$this->cart) {
            return [];
        }

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
        if (!$this->cart) {
            return 0;
        }

        $this->loadItems();

        return $this->cart->items->sum('quantity');
    }

    /**
     * Initiate the payment payment driver
     */
    public function initPayment()
    {
        if (!$this->cart) {
            return;
        }

        $this->loadItemData();
        if ($this->cartWasInvalid) {
            throw new AjaxException('CART_INVALID');
        }

        if ($this->cart->shipping_rates) {
            $this->selectShipping();
        }

        if (!$gateway = PaymentSettings::getGateway($this->cart)) {
            throw new AjaxException('GATEWAY_NOT_FOUND');
        }

        return $gateway->executePayment();
    }

    /**
     * Removes an Address from the cart
     */
    public function removeAddress()
    {
        $this->loadCart();

        $this->cart->address = null;

        $this->actionCompleted();
    }

    /**
     * Removes a Customer from the cart
     */
    public function removeCustomer()
    {
        $this->loadCart();

        $this->cart->customer_id = null;

        $this->actionCompleted();
    }

    /**
     * Removes one or more items from the cart
     *
     * @param   integer|arrayarray     $itemIds
     */
    public function removeItems($itemIds)
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

        $this->actionCompleted();
    }

    /**
     * Removes a promotion
     */
    public function removePromotion()
    {
        $this->loadCart();

        $this->cart->promotion_id = null;

        $this->actionCompleted();
    }

    /**
     * Select a shipping rate
     *
     * @return  boolean
     */
    public function selectShipping()
    {
        // todo: write a test for this

        // If a rate was selected, look for it in the quoted rates
        $rates = $this->cart->shipping_rates;
        if ($selected = input('shipping')) {
            foreach ($rates as $i => $rate) {
                if ($rate['id'] == $selected) {
                    $this->cart->shipping_id = $selected;
                    return $this->cart->save();
                }
            }
        }

        // Otherwise pick the cheapest rate
        usort($rates, function($a, $b) {
            return $a['cost'] - $b['cost'];
        });

        $this->cart->shipping_id = $rates[0]['id'];
        return $this->cart->save();
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
                $this->actionCompleted();
            }
        } catch (Exception $e) {
            throw new AjaxException($e->getMessage());
        }
    }

    /**
     * Updates items in the cart
     *
     * @param  void|array   $items
     */
    public function updateItems($items = [])
    {
        $this->loadCart();

        // Determine if anything has actually changed
        $this->loadItems();
        $updated = false;
        foreach ($this->cart->items as $cartItem) {
            if (!array_key_exists($cartItem->id, $items)) {
                continue;
            }

            if ($cartItem->quantity != $items[$cartItem->id]) {
                $updated = true;
            }
        }

        if (!$updated) {
            return;
        }

        // Update the new values
        $this->cart->items->load('inventory');
        foreach ($this->cart->items as $cartItem) {
            if (!array_key_exists($cartItem->id, $items)) {
                continue;
            }

            $cartItem->quantity = $items[$cartItem->id];
            if ($cartItem->quantity > $cartItem->inventory->quantity) {
                $cartItem->quantity = $cartItem->inventory->quantity;
            }

            $cartItem->save();
        }

        $this->actionCompleted();
    }
}
