<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Status;
use Lang;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultStatuses extends Seeder
{
    public function run()
    {
        Status::create([
            'name'      => Lang::get('bedard.shop::lang.statuses.defaults.awaiting_payment'),
            'icon'      => 'icon-spinner spinning',
            'inventory' => 0,
            'is_pending'=> true,
        ]);

        Status::create([
            'name'      => Lang::get('bedard.shop::lang.statuses.defaults.payment_received'),
            'icon'      => 'icon-money',
            'inventory' => -1,
            'color'     => '#3498db',
        ]);

        Status::create([
            'name'      => Lang::get('bedard.shop::lang.statuses.defaults.canceled'),
            'icon'      => 'icon-minus',
            'inventory' => 1,
            'color'     => '#f39c12',
        ]);

        Status::create([
            'name'      => Lang::get('bedard.shop::lang.statuses.defaults.abandoned'),
            'icon'      => 'icon-times',
            'inventory' => 1,
            'color'     => '#c0392b',
        ]);

        Status::create([
            'name'      => Lang::get('bedard.shop::lang.statuses.defaults.complete'),
            'icon'      => 'icon-check',
            'inventory' => -1,
            'color'     => '#27ae60',
        ]);
    }
}
