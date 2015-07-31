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

        $shipping = $this->cart->getSelectedShipping();
        $order->shipping_total      = $shipping['cost'];
        $order->shipping_driver     = $shipping['driver'];
        $order->shipping_name       = $shipping['name'];
        $order->shipping_original   = isset($shipping['original'])
            ? $shipping['original']
            : 0;

        $order->promotion_total     = $this->cart->promotionSavings;
        $order->payment_total       = $this->cart->total;
        $order->save();

        return $order;
    }

    /**
     * Adjusts the inventory up or down based on the status behavior.
     *
     * @param   Status  $status
     */
    protected function adjustInventory(Status $status)
    {
        if ($status->inventory === 1) {
            $this->inventory->up();
        }

        elseif ($status->inventory === -1) {
            $this->inventory->down();
        }
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
    public function begin(Status $status = null)
    {
        $order = $this->createOrder();

        if ($status) {
            $this->adjustInventory($status);
            $order->changeStatus($status, $this->driver);
        }

        $this->cart->status = 'paying';
        $this->cart->save();
    }

    /**
     * Cancel a payment
     */
    public function cancel(Status $status = null)
    {
        $order = $this->getOrder();

        if ($status) {
            $this->adjustInventory($status);
            $order->changeStatus($status, $this->driver);
        }

        $this->cart->status = 'canceled';
        $this->cart->save();
    }

    /**
     * Complete a payment
     */
    public function complete(Status $status = null)
    {
        $order = $this->getOrder();

        if ($status) {
            $this->adjustInventory($status);
            $order->is_paid = true;
            $order->changeStatus($status, $this->driver);
        }

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
