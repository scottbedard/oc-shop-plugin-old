<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateInventoryValueTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_inventory_value', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('inventory_id')->unsigned()->nullable();
            $table->integer('value_id')->unsigned()->nullable();
            $table->primary(['inventory_id', 'value_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_inventory_value');
    }

}
