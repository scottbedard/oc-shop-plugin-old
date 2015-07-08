<?php namespace Bedard\Shop\Traits;

use App;

/**
 * Provides methods for components to access cart information without firing
 * duplicate or unecessary queries.
 */
trait CartAccessTrait {

    /**
     * @var CartManager     The cart manager instance
     */
    protected $manager;

    /**
     * @var CartModel       The user's shopping cart
     */
    public $cart;

    public function init()
    {
        $this->manager = App::make('Bedard\Shop\Classes\CartManager');
    }

    public function prepareCart()
    {
        $this->cart = $this->manager->cart;
    }

    /**
     * Determines if the cart was invalid
     *
     * @return  boolean
     */
    public function cartWasInvalid()
    {
        return $this->manager->cartWasInvalid;
    }

    /**
     * Returns all items in the cart
     *
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public function getItems()
    {
        return $this->manager->getItems();
    }

    /**
     * Returns the sum of CartItem quantities
     *
     * @return  integer
     */
    public function itemCount()
    {
        return $this->manager->getItemCount();
    }

    /**
     * Determines if the cart is empty or not
     *
     * @return  boolean
     */
    public function isEmpty()
    {
        return $this->manager->getItemCount() == 0;
    }

}
