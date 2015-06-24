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
            $table->string('key')->nullable();
            $table->string('hash')->nullable();
            $table->integer('promotion_id')->unsigned()->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_carts');
    }

}