<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Status;
use Lang;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultStatuses extends Seeder
{
    public function run()
    {
        Status::create([
            'name'          => Lang::get('bedard.shop::lang.statuses.defaults.awaiting_payment'),
            'icon'          => 'icon-spinner',
            'class'         => 'spinning',
            'inventory'     => 0,
            'core_status'   => 'started',
        ]);

        Status::create([
            'name'          => Lang::get('bedard.shop::lang.statuses.defaults.payment_received'),
            'icon'          => 'icon-money',
            'class'         => 'blue',
            'inventory'     => -1,
            'core_status'   => 'received',
        ]);

        Status::create([
            'name'          => Lang::get('bedard.shop::lang.statuses.defaults.canceled'),
            'icon'          => 'icon-minus',
            'core_status'   => 'canceled',
            'inventory'     => 1,
        ]);

        Status::create([
            'name'          => Lang::get('bedard.shop::lang.statuses.defaults.abandoned'),
            'icon'          => 'icon-times',
            'class'         => 'red',
            'inventory'     => 1,
            'core_status'   => 'abandoned',
        ]);

        Status::create([
            'name'          => Lang::get('bedard.shop::lang.statuses.defaults.complete'),
            'icon'          => 'icon-check',
            'class'         => 'green',
            'inventory'     => -1,
        ]);
    }
}
