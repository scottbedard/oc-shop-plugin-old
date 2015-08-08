<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStatusesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_statuses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();
            $table->tinyInteger('inventory')->default(0);
            $table->boolean('is_pending')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_statuses');
    }

}
