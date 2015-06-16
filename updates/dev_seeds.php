<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Tests\Fixtures\Generate;
use October\Rain\Database\Updates\Seeder;

class DevSeeds extends Seeder
{
    public function run()
    {
        // Don't run seeds during unit tests
        if (app()->env == 'testing') {
            return false;
        }

        $this->seedCategories();
        $this->seedProducts();
        $this->seedDiscounts();
    }

    public function seedCategories()
    {
        $all        = Generate::category('All',         ['position' => 1, 'filter' => 'all']);
        $clothing   = Generate::category('Clothing',    ['position' => 2]);
        $summer     = Generate::category('Summer',      ['position' => 3, 'parent_id' => $clothing->id]);
        $sandals    = Generate::category('Sandals',     ['position' => 4, 'parent_id' => $summer->id]);
        $shirts     = Generate::category('Shirts',      ['position' => 5, 'parent_id' => $summer->id]);
        $tshirts    = Generate::category('T-Shirts',    ['position' => 6, 'parent_id' => $shirts->id]);
        $tanks      = Generate::category('Tanks',       ['position' => 7, 'parent_id' => $shirts->id]);
        $shorts     = Generate::category('Shorts',      ['position' => 8, 'parent_id' => $summer->id]);
        $winter     = Generate::category('Winter',      ['position' => 9, 'parent_id' => $clothing->id]);
        $hats       = Generate::category('Hats',        ['position' => 10, 'parent_id' => $winter->id]);
        $jackets    = Generate::category('Jackets',     ['position' => 11, 'parent_id' => $winter->id]);
    }

    public function seedProducts()
    {
        $adjectives = ['awesome', 'crappy', 'average', 'red', 'blue', 'green', 'white'];

        foreach ($adjectives as $adjective) {
            $shirt = Generate::product(ucfirst($adjective).' shirt', ['is_active' => (bool) rand(0, 8), 'base_price' => rand(5, 15)]);
            $shirt->categories()->sync([rand(5, 7)]);
            $this->seedInventories($shirt);

            $hat = Generate::product(ucfirst($adjective).' hat', ['is_active' => (bool) rand(0, 8), 'base_price' => rand(5, 15)]);
            $hat->categories()->sync([10]);
            $this->seedInventories($hat);

            $jacket = Generate::product(ucfirst($adjective).' jacket', ['is_active' => (bool) rand(0, 8), 'base_price' => rand(5, 15)]);
            $hat->categories()->sync([11]);
            $this->seedInventories($jacket);
        }
    }

    public function seedInventories($product)
    {
        // Generate options and inventories
        if (rand(0,1)) {
            $size   = Generate::option('Size', ['product_id' => $product->id]);
            $small  = Generate::value($size, 'Small', ['position' => 0]);
            $medium = Generate::value($size, 'Medium', ['position' => 1]);
            $large  = Generate::value($size, 'Large', ['position' => 2]);
            $inv1   = Generate::inventory($product, [$small->id], ['quantity' => rand(0, 2)]);
            $inv2   = Generate::inventory($product, [$medium->id], ['quantity' => rand(0, 2)]);
            $inv3   = Generate::inventory($product, [$large->id], ['quantity' => rand(0, 2)]);
        }

        // Generate inventory for the default option
        else {
            $inv    = Generate::inventory($product, [], ['quantity' => rand(0, 2)]);
        }
    }

    public function seedDiscounts()
    {
        $discount = Generate::discount('Shirt Discount', [
            'amount_percentage' => 25,
            'is_percentage'     => true,
        ]);

        $discount->categories()->sync([5]);
        $discount->load('categories');
        $discount->syncProducts();
    }
}
