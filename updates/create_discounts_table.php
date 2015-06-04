<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDiscountsTable extends Migration
{

    public function up()
    {
        Schema::create('bedard_shop_discounts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->decimal('amount_exact', 10, 2)->default(0);
            $table->tinyInteger('amount_percentage')->default(0);
            $table->boolean('is_percentage')->default(true);
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_discounts');
    }

}
