<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDiscountCategoryTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_discount_category', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['discount_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_discount_category');
    }

}
