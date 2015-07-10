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

        // No Payment
        $nopayment = new Driver;
        $nopayment->name = 'bedard.shop::lang.drivers.nopayment.name';
        $nopayment->type = 'payment';
        $nopayment->class = 'Bedard\Shop\Drivers\Payment\NoPayment';
        $nopayment->save();

        // PayPal Express
        $paypal = new Driver;
        $paypal->name    = 'Paypal Express';
        $paypal->type    = 'payment';
        $paypal->class   = 'Bedard\Shop\Drivers\Payment\PaypalExpress';
        $paypal->is_configurable = true;
        $paypal->save();
        $paypal->image()->add($this->makeFile('bedard/shop/assets/images/paypal.png'));

        // Stripe
        $stripe = new Driver;
        $stripe->name    = 'Stripe';
        $stripe->type    = 'payment';
        $stripe->class   = 'Bedard\Shop\Drivers\Payment\Stripe';
        $stripe->is_configurable = true;
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
