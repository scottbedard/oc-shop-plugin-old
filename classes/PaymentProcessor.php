<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\InventoryManager;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\PaymentSettings;

class PaymentProcessor {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var InventoryManager
     */
    protected $inventory;

    /**
     * Constructor
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
        $this->inventory = new InventoryManager($cart);
    }

    /**
     * Begin a payment
     */
    public function begin()
    {
        if (PaymentSettings::getTiming() == 'immediate') {
            $this->inventory->down();
        }

        $this->cart->status = 'paying';
        $this->cart->save();
    }

    /**
     * Cancel a payment
     */
    public function cancel()
    {
        $this->inventory->up();
        $this->cart->status = 'canceled';
        $this->cart->save();
    }

    /**
     * Complete a payment
     *
     * @param   integer     $payment_driver_id
     */
    public function complete($payment_driver_id = null)
    {
        // Only cache the relevant cart data
        $this->cart->load([
            'items' => function($item) {
                $item->withTrashed()->with([
                    'inventory' => function($inventory) {
                        $inventory->addSelect('id', 'product_id', 'sku', 'modifier')->with([
                            'product' => function($product) {
                                $product->joinPrices()->addSelect('id', 'name', 'base_price', 'price');
                            },
                            'values' => function($values) {
                                $values->addSelect('id', 'option_id', 'name')->with(['option' => function($option) {
                                    $option->addSelect('id', 'name');
                                }]);
                            }
                        ]);
                    }
                ]);
            },
            'promotion',
        ]);

        $order = new Order;
        $order->payment_driver_id = $payment_driver_id;
        $order->cart_cache        = $this->cart->toArray();
        $order->cart_subtotal     = $this->cart->subtotal;
        $order->shipping_total    = $this->cart->shipping_cost;
        $order->promotion_total   = $this->cart->promotionSavings;
        $order->payment_total     = $this->cart->total;
        $order->save();

        $this->inventory->down();
        $this->cart->status = 'complete';
        $this->cart->save();
    }

    public function error()
    {
        $this->inventory->up();
        $this->cart->status = 'error';
        $this->cart->save();
    }

    // protected function cacheCart()
    // {
    //     $cache = [
    //         'items' => [],
    //         'products' => [],
    //         'promotion' => [],
    //     ];
    //
    //     foreach ($this->cart->items as $item) {
    //         $cache['items'][] = [
    //             'id'            => $item->id,
    //             'product_id'    => $item->product_id,
    //             'quantity'      => $item->quantity,
    //         ];
    //     }
    //
    //     return $cache;
    // }
}
