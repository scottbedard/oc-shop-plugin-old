<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDiscountablesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_discountables', function($table)
        {
            $table->enging = 'InnoDB';
            $table->integer('discount_id')->unsigned()->nullable();
            $table->integer('discountable_id')->unsigned()->nullable();
            $table->string('discountable_type')->nullable();
            $table->unique(['discount_id', 'discountable_id', 'discountable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_discountables');
    }

}
