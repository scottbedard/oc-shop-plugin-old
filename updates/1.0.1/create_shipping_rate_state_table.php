<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateShippingRateStateTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_shipping_rate_state', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('rate_id')->unsigned()->nullable();
            $table->integer('state_id')->unsigned()->nullable();
            $table->primary(['rate_id', 'state_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_shipping_rate_state');
    }

}
