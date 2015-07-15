<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\CartCache;
use Bedard\Shop\Classes\InventoryManager;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Status;

class PaymentProcessor {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var InventoryManager
     */
    protected $inventory;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Constructor
     */
    public function __construct(Cart $cart, Driver $driver = null, Order $order = null)
    {
        $this->cart         = $cart;
        $this->driver       = $driver;
        $this->order        = $order;
        $this->inventory    = new InventoryManager($cart);
    }

    /**
     * Create a new order
     *
     * @return  Order
     */
    protected function createOrder()
    {
        $order = Order::firstOrNew(['cart_id' => $this->cart->id]);

        if ($this->driver) {
            $order->payment_driver_id = $this->driver->id;
        }

        $order->customer_id         = $this->cart->customer_id;
        $order->shipping_address_id = $this->cart->shipping_address_id;
        $order->billing_address_id  = $this->cart->billing_address_id;
        $order->cart_cache          = (new CartCache)->cache($this->cart);
        $order->cart_subtotal       = $this->cart->subtotal;
        $order->shipping_total      = $this->cart->shipping_cost;
        $order->promotion_total     = $this->cart->promotionSavings;
        $order->payment_total       = $this->cart->total;
        $order->save();

        return $order;
    }

    /**
     * Get the order if one exists, otherwise create one
     *
     * @return  Order
     */
    public function getOrder()
    {
        if ($this->order) {
            return $this->order;
        }

        elseif ($this->cart->order) {
            return $this->cart->order;
        }

        return $this->createOrder();
    }

    /**
     * Abandon an order
     */
    public function abandon(Status $status = null)
    {
        if (is_null($status)) {
            $status = Status::getCore('abandoned');
        }

        $order = $this->getOrder();
        $order->changeStatus($status, $this->driver);
        $this->inventory->up();
    }

    /**
     * Begin a payment
     */
    public function begin()
    {
        if (PaymentSettings::getTiming() == 'immediate') {
            $this->inventory->down();
        }

        $order = $this->createOrder();
        if ($status = Status::getCore('started')) {
            $order->changeStatus($status, $this->driver);
        }

        $this->cart->status = 'paying';
        $this->cart->save();
    }

    /**
     * Cancel a payment
     */
    public function cancel()
    {
        $order = $this->getOrder();
        if ($status = Status::getCore('canceled')) {
            $order->changeStatus($status, $this->driver);
        }

        $this->inventory->up();
        $this->cart->status = 'canceled';
        $this->cart->save();
    }

    /**
     * Complete a payment
     */
    public function complete()
    {
        $order = $this->getOrder();
        if ($status = Status::getCore('received')) {
            $order->is_paid = true;
            $order->changeStatus($status, $this->driver);
        }

        $this->inventory->down();
        $this->cart->status = 'complete';
        $this->cart->save();
    }

    /**
     * A payment error occured
     */
    public function error()
    {
        $this->inventory->up();
        $this->cart->status = 'error';
        $this->cart->save();
    }
}
