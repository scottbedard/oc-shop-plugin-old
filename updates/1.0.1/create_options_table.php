<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOptionsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_options', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable()->index();
            $table->string('name')->nullable();
            $table->string('placeholder')->nullable();
            $table->integer('position')->unsigned()->default(0);
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('payment_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_options');
    }

}
