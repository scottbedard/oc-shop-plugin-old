<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Driver;
use System\Models\File;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultDrivers extends Seeder
{
    public function run()
    {
        // Standard shipping calculator
        $table = new Driver;
        $table->name    = 'Shipping Table';
        $table->type    = 'shipping';
        $table->class   = 'Bedard\Shop\Drivers\Shipping\BasicTable';
        $table->save();

        // PayPal Express
        $paypal = new Driver;
        $paypal->name    = 'Paypal Express';
        $paypal->type    = 'payment';
        $paypal->class   = 'Bedard\Shop\Drivers\Payment\PaypalExpress';
        $paypal->save();
        $paypal->image()->add($this->makeFile('bedard/shop/assets/images/paypal.png'));

        // Stripe
        $stripe = new Driver;
        $stripe->name    = 'Stripe';
        $stripe->type    = 'payment';
        $stripe->class   = 'Bedard\Shop\Drivers\Payment\Stripe';
        $stripe->save();
        $stripe->image()->add($this->makeFile('bedard/shop/assets/images/stripe.png'));
    }

    protected function makeFile($path)
    {
        $file = new File;
        $file->fromFile(plugins_path($path));
        $file->save();
        return $file;
    }
}
