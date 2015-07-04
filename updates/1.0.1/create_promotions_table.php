<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePromotionsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_promotions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('message')->nullable();
            $table->decimal('cart_exact', 10, 2)->default(0);
            $table->tinyInteger('cart_percentage')->default(0);
            $table->boolean('is_cart_percentage')->default(true);
            $table->decimal('shipping_exact', 10, 2)->default(0);
            $table->tinyInteger('shipping_percentage')->default(0);
            $table->boolean('is_shipping_percentage')->default(true);
            $table->decimal('cart_minimum', 10, 2)->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_promotions');
    }

}
