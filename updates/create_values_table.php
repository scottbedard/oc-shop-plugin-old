<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateValuesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_values', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('option_id')->unsigned()->nullable()->index();
            $table->string('name')->nullable();
            $table->integer('position')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_values');
    }

}
