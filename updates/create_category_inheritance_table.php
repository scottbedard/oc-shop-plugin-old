<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoryInheritanceTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_category_inheritance', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('parent_id')->unsigned();
            $table->integer('inherited_id')->unsigned();
            $table->primary(['parent_id', 'inherited_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_category_inheritance');
    }

}
