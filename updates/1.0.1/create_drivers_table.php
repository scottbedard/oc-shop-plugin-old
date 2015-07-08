<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDriversTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_drivers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('class')->nullable();
            $table->string('type')->nullable();
            $table->text('config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_drivers');
    }

}
