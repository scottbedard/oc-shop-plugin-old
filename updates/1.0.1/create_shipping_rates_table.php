<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateShippingRatesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_shipping_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned()->nullable()->index();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_shipping_rates');
    }

}
