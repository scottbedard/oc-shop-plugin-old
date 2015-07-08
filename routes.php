<?php

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\PaymentSettings;

/**
 * Payment response routes
 *
 * @return  Redirect
 */
Route::get('bedard/shop/payments/{cart}/{driver}/{hash}/{status}', ['as' => 'bedard.shop.payments',
    function($cart_id, $driver_id, $hash, $status) {
        $cart = Cart::where('hash', $hash)->isPaying()->find($cart_id);
        $payment = new PaymentProcessor($cart);
        if ($status == 'success') {
            $payment->complete();
            return Redirect::to(PaymentSettings::getSuccessUrl());
        } elseif ($status == 'canceled') {
            $payment->cancel();
            return Redirect::to(PaymentSettings::getCanceledUrl());
        } else {
            $payment->error();
            return Redirect::to(PaymentSettings::getErrorUrl());
        }
    }
]);
