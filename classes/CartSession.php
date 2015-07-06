<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Settings;
use Cookie;
use October\Rain\Exception\AjaxException;
use Session;
use Request;

class CartSession {

    /**
     * @var string      The identifying cart key for sessions and cookies
     */
    const CART_KEY = 'bedard_shop_cart';

    /**
     * @var Cart        The user's shopping cart
     */
    public $cart;

    /**
     * Open the current cart if one exists
     */
    public function __construct()
    {
        // First, attempt to load the cart from the user's session
        if ($session = Session::get(self::CART_KEY)) {
            $this->cart = Cart::where('key', $session['key'])
                ->find($session['id']);
                // todo: make sure cart is open
        }

        // If that fails, check if we have the cart data saved in a cookie
        elseif (Settings::getCartLife() !== false && ($cookie = Request::cookie(self::CART_KEY))) {
            $this->cart = Cart::where('key', $cookie['key'])
                ->find($cookie['id']);
                // todo: make sure cart is open
        }

        // If we still don't have a cart, forget the session and cookie to
        // prevent queries looking for a cart that doesn't exist.
        if (!$this->cart) {
            $this->forgetCart();
        }
    }

    /**
     * Creates a new cart and session
     */
    protected function createCart()
    {
        $cart = Cart::create(['key' => str_random(40)]);

        Session::put(self::CART_KEY, [
            'id'    => $cart->id,
            'key'   => $cart->key
        ]);

        $this->cart = $cart;
    }

    /**
     * Forget the cart session and cookie
     */
    protected function forgetCart()
    {
        Session::forget(self::CART_KEY);
        Cookie::queue(Cookie::forget(self::CART_KEY));
    }

    /**
     * Creates a cart if one doesn't exist yet, and refreshes the cart cookie
     */
    public function loadCart()
    {
        if (!$this->cart) {
            $this->createCart();
        }

        // If we still don't have a cart, forget the cart and throw an exception
        if (!$this->cart) {
            $this->forgetCart();
            throw new AjaxException('CART_INVALID');
        }

        // Refresh cart cookie
        if ($life = Settings::getCartLife()) {
            Cookie::queue(self::CART_KEY, [
                'id'    => $this->cart->id,
                'key'   => $this->cart->key,
            ], $life);
        }
    }
}
