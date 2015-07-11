<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Status;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultStatuses extends Seeder
{
    public function run()
    {
        // Don't run seeds during unit tests
        if (app()->env == 'testing') {
            return false;
        }

        Status::create([
            'name'  => 'bedard.shop::lang.statuses.awaiting_payment',
            'icon'  => 'icon-spinner',
            'class' => 'spinning',
        ]);

        Status::create([
            'name'  => 'bedard.shop::lang.statuses.payment_received',
            'icon'  => 'icon-money',
            'class' => 'blue',
        ]);

        Status::create([
            'name'  => 'bedard.shop::lang.statuses.canceled',
            'icon'  => 'icon-minus',
        ]);

        Status::create([
            'name'  => 'bedard.shop::lang.statuses.abandoned',
            'icon'  => 'icon-times',
            'class' => 'red'
        ]);

        Status::create([
            'name'  => 'bedard.shop::lang.statuses.complete',
            'icon'  => 'icon-check',
            'class' => 'green',
        ]);
    }
}
