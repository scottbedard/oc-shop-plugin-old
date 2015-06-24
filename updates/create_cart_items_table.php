<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCartItemsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_cart_items', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('cart_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('inventory_id')->unsigned()->nullable();
            $table->integer('quantity')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_cart_items');
    }

}
