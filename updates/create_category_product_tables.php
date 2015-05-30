<?php namespace Bedard\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoryProductTables extends Migration
{

    public function up()
    {
        // This table exists to hold the literal data of which products
        // belong to which categories. This is used almost exclusively
        // for backend forms.
        Schema::create('bedard_shop_cat_prod', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('category_id')->unsigned()->default(0);
            $table->integer('product_id')->unsigned()->default(0);
            $table->primary(['category_id', 'product_id']);
        });

        // This table exists to determine which products should be displayed
        // in which categories. For example, a "Clothing" category might
        // have a "Shirts" category nested under it. If "Clothing" is inheriting
        // it's child products, then a product of "Shirts" would be listed
        // twice in this table. Once under "Shirts", and once under "Clothing".
        Schema::create('bedard_shop_cat_prod_display', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('category_id')->unsigned()->default(0);
            $table->integer('product_id')->unsigned()->default(0);
            $table->primary(['category_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bedard_shop_cat_prod');
        Schema::dropIfExists('bedard_shop_cat_prod_display');
    }

}
