<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDiscountProductTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_discount_product', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->primary(['discount_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_discount_product');
    }

}
