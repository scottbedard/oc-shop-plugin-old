<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateInventoriesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_inventories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable()->index();
            $table->string('sku')->nullable()->unique();
            $table->integer('quantity')->unsigned()->default(0);
            $table->decimal('modifier', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_inventories');
    }

}
