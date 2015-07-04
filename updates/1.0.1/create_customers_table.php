<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCustomersTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_customers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_customers');
    }

}
