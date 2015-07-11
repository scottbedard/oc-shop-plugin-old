<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStatusEventsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_status_events', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('order_id')->unsigned()->index();
            $table->integer('status_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->timestamp('status_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_status_events');
    }

}
