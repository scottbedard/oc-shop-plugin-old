<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Driver;
use System\Models\File;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultDrivers extends Seeder
{
    public function run()
    {
        // Standard shipping calculator
        Driver::create([
            'name'          => 'Shipping Table',
            'type'          => 'shipping',
            'class'         => 'Bedard\Shop\Drivers\Shipping\BasicTable',
            'is_default'    => true,
        ]);

        // PayPal Express
        $paypal = Driver::create([
            'name'          => 'Paypal Express',
            'type'          => 'payment',
            'class'         => 'Bedard\Shop\Drivers\Payment\PaypalExpress',
            'is_default'    => true,
        ]);

        $logo = new File;
        $logo->fromFile(plugins_path('bedard/shop/assets/images/paypal.png'));
        $logo->save();
        $paypal->image()->add($logo);

        // Stripe
        $stripe = Driver::create([
            'name'          => 'Stripe',
            'type'          => 'payment',
            'class'         => 'Bedard\Shop\Drivers\Payment\Stripe',
            'is_default'    => true,
        ]);

        $logo = new File;
        $logo->fromFile(plugins_path('bedard/shop/assets/images/stripe.png'));
        $logo->save();
        $stripe->image()->add($logo);
    }
}
