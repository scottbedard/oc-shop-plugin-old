<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCountryPromotionTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_country_promotion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('country_id')->unsigned()->nullable();
            $table->integer('promotion_id')->unsigned()->nullable();
            $table->primary(['country_id', 'promotion_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_country_promotion');
    }

}
