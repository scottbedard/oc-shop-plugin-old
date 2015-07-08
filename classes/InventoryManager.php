<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;
use Queue;

class InventoryManager {

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Constructor
     *
     * @param   Cart    $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Adjust the available inventory up or down
     *
     * @param   string  $direction
     */
    protected function adjustInventory($direction)
    {
        $cart_id = $this->cart->id;
        Queue::push(function($job) use($cart_id, $direction) {
            $cart = Cart::with('items.inventory')->find($cart_id);
            foreach ($cart->items as $item) {
                if ($direction == 'up') {
                    $item->inventory->quantity += $item->quantity;
                } elseif ($direction == 'down') {
                    $item->inventory->quantity -= $item->quantity;
                }
                $item->inventory->save();
            }

            $cart->is_inventoried = $direction == 'down';
            $cart->save();
            $job->delete();
        });
    }

    /**
     * Removes inventory
     */
    public function down()
    {
        if ($this->cart->is_inventoried) {
            return;
        }

        $this->adjustInventory('down');
    }

    /**
     * Restocks inventory
     */
    public function up()
    {
        if (!$this->cart->is_inventoried) {
            return;
        }

        $this->adjustInventory('up');
    }
}
