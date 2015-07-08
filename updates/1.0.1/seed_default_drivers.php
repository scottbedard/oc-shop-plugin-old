<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Driver;
use System\Models\File;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultDrivers extends Seeder
{
    public function run()
    {
        // Standard shipping calculator
        $table = Driver::create([
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

        $logo = $this->makeFile('bedard/shop/assets/images/paypal.png');
        $paypal->image()->add($logo);

        // Stripe
        $stripe = Driver::create([
            'name'          => 'Stripe',
            'type'          => 'payment',
            'class'         => 'Bedard\Shop\Drivers\Payment\Stripe',
            'is_default'    => true,
        ]);

        $logo = $this->makeFile('bedard/shop/assets/images/stripe.png');
        $stripe->image()->add($logo);
    }

    protected function makeFile($path)
    {
        $file = new File;
        $file->fromFile(plugins_path($path));
        $file->save();
        return $file;
    }
}
