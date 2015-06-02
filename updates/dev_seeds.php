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
            $shirt = Generate::product(ucfirst($adjective).' shirt', ['base_price' => rand(5, 15)]);
            $shirt->categories()->sync([rand(5, 7)]);

            $hat = Generate::product(ucfirst($adjective).' hat', ['base_price' => rand(5, 15)]);
            $hat->categories()->sync([10]);

            $jacket = Generate::product(ucfirst($adjective).' jacket', ['base_price' => rand(5, 15)]);
            $hat->categories()->sync([11]);
        }
    }
}
