<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateShippingCountryRateTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_shipping_country_rate', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('country_id')->unsigned()->nullable();
            $table->integer('rate_id')->unsigned()->nullable();
            $table->primary(['country_id', 'rate_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_shipping_country_rate');
    }

}
