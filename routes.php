<?php

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
        $driver = Driver::find($driver_id);

        $class = $driver->getClass();
        $class->setCart($cart);
        $class->setDriver($driver);

        if ($status == 'success') {
            $class->completePaymentProcess();
            return Redirect::to(PaymentSettings::getSuccessUrl());
        } elseif ($status == 'canceled') {
            $class->cancelPaymentProcess();
            return Redirect::to(PaymentSettings::getCanceledUrl());
        } else {
            $class->errorPaymentProcess();
            return Redirect::to(PaymentSettings::getErrorUrl());
        }
    }
]);
