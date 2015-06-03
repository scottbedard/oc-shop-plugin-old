<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePricesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_prices', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('discount_id')->unsigned()->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->primary(['product_id', 'discount_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_prices');
    }

}
