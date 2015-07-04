<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductPromotionTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_product_promotion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('promotion_id')->unsigned();
            $table->primary(['product_id', 'promotion_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_product_promotion');
    }

}
