<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePaymentsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_payments', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('cart_id')->unsigned()->nullable()->index();
            $table->boolean('is_inventoried')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_payments');
    }

}
