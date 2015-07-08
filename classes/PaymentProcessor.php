<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Classes\InventoryManager;
use Bedard\Shop\Models\Cart;
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
     */
    public function complete()
    {
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
