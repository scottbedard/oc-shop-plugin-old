<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\InventoryManager;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Payment;
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
        // todo: narrow selection to only include relevant data
        $this->cart->load([
            'items.inventory.product',
            'items.inventory.values.option',
            'address',
            'customer',
            'promotion',
        ]);

        $payment = new Payment;
        $payment->payment_driver_id = $payment_driver_id;
        $payment->cart_cache        = $this->cart->toArray();
        $payment->cart_subtotal     = $this->cart->subtotal;
        $payment->shipping_total    = $this->cart->shipping_cost;
        $payment->promotion_total   = $this->cart->promotionSavings;
        $payment->payment_total     = $this->cart->total;
        $payment->save();

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
}
