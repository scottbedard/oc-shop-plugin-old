<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoryProductTables extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_category_product', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('category_id')->unsigned()->default(0);
            $table->integer('product_id')->unsigned()->default(0);
            $table->primary(['category_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_category_product');
    }

}
