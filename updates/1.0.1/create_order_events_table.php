<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOrderEventsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_order_events', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('order_id')->unsigned()->nullable()->index();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('driver_id')->unsigned()->nullable()->index();
            $table->string('message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_order_events');
    }

}
