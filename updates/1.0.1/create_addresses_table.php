<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateAddressesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_addresses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('organization')->nullable();
            $table->string('street_1')->nullable();
            $table->string('street_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state_name')->nullable();
            $table->integer('state_id')->unsigned()->nullable()->index();
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->boolean('is_billing')->default(true);
            $table->boolean('is_shipping')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_addresses');
    }

}
