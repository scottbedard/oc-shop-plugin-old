<?php namespace Bedard\Shop\Classes;

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
     * Constructor
     */
    public function __construct(Cart $cart, Driver $driver = null)
    {
        $this->cart = $cart;
        $this->driver = $driver;
        $this->inventory = new InventoryManager($cart);
    }

    /**
     * Create a new order
     *
     * @return  Order
     */
    protected function createOrder()
    {
        $this->cart->loadOrderCache();

        $order = new Order;

        if ($this->driver) {
            $order->payment_driver_id = $this->driver->id;
        }

        $order->customer_id         = $this->cart->customer_id;
        $order->shipping_address_id = $this->cart->shipping_address_id;
        $order->billing_address_id  = $this->cart->billing_address_id;
        $order->cart_id             = $this->cart->id;
        $order->cart_cache          = $this->cart->toArray();
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
        return $this->cart->order ?: $this->createOrder();
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
            $order->changeStatus($status->id, $this->driver);
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
     */
    public function complete()
    {
        $order = $this->getOrder();
        if ($status = Status::getCore('received')) {
            $order->changeStatus($status->id, $this->driver);
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
