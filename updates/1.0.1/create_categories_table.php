<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable()->index();
            $table->integer('position')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('sort_key')->nullable()->default('created_at');
            $table->string('sort_order')->nullable()->default('desc');
            $table->tinyInteger('columns')->unsigned()->default(4);
            $table->tinyInteger('rows')->unsigned()->default(3);
            $table->string('filter')->nullable();
            $table->decimal('filter_value', 10, 2)->default(0);
            $table->boolean('hide_out_of_stock')->default(false);
            $table->boolean('is_inheriting')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_categories');
    }

}
