<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCartsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_carts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('status')->nullable()->default('open');
            $table->string('key')->nullable();
            $table->string('hash')->nullable();
            $table->integer('customer_id')->unsigned()->nullable()->index();
            $table->integer('shipping_address_id')->unsigned()->nullable()->index();
            $table->integer('billing_address_id')->unsigned()->nullable()->index();
            $table->integer('promotion_id')->unsigned()->nullable()->index();
            $table->text('shipping_rates')->nullable();
            $table->string('shipping_id')->nullable();
            $table->boolean('shipping_failed')->default(false);
            $table->boolean('is_inventoried')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_carts');
    }

}
