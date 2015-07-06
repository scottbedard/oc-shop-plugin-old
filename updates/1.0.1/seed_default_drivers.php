<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Driver;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultDrivers extends Seeder
{
    public function run()
    {
        // Standard shipping calculator
        Driver::create([
            'name'          => 'Shipping Table',
            'type'          => 'shipping',
            'class'         => 'Bedard\Shop\Classes\ShippingTable',
            'is_enabled'    => true,
        ]);

        // PayPal Express
        Driver::create([
            'name'          => 'PayPal Express',
            'type'          => 'payment',
            'class'         => 'Bedard\Shop\Payment\PaypalExpress',
            'is_enabled'    => true,
        ]);
    }
}
