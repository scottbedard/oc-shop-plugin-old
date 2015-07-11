<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Status;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultStatuses extends Seeder
{
    public function run()
    {
        Status::create([
            'name'          => 'bedard.shop::lang.statuses.awaiting_payment',
            'icon'          => 'icon-spinner',
            'class'         => 'spinning',
            'core_status'   => 'started',
        ]);

        Status::create([
            'name'          => 'bedard.shop::lang.statuses.payment_received',
            'icon'          => 'icon-money',
            'class'         => 'blue',
            'core_status'   => 'received',
        ]);

        Status::create([
            'name'          => 'bedard.shop::lang.statuses.canceled',
            'icon'          => 'icon-minus',
            'core_status'   => 'canceled',
        ]);

        Status::create([
            'name'          => 'bedard.shop::lang.statuses.abandoned',
            'icon'          => 'icon-times',
            'class'         => 'red',
            'core_status'   => 'abandoned',
        ]);

        Status::create([
            'name'          => 'bedard.shop::lang.statuses.complete',
            'icon'          => 'icon-check',
            'class'         => 'green',
        ]);
    }
}
