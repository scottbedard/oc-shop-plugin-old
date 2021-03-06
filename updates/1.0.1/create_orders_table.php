<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePaymentsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_orders', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('cart_id')->unsigned()->nullable()->index();
            $table->integer('customer_id')->unsigned()->nullable()->index();
            $table->integer('shipping_address_id')->unsigned()->nullable();
            $table->integer('billing_address_id')->unsigned()->nullable();
            $table->integer('shipping_driver_id')->unsigned()->nullable();
            $table->integer('payment_driver_id')->unsigned()->nullable();
            $table->json('cart_cache')->nullable();
            $table->decimal('cart_subtotal', 10, 2)->default(0);
            $table->string('shipping_driver')->nullable();
            $table->string('shipping_name')->nullable();
            $table->decimal('shipping_original', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('promotion_total', 10, 2)->default(0);
            $table->decimal('payment_total', 10, 2)->default(0);
            $table->integer('status_id')->nullable();
            $table->timestamp('status_at')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_orders');
    }

}
