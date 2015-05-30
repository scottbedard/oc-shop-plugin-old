<?php namespace RainLab\Blog\Updates;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
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
        $all        = Category::create(['name' => 'All', 'slug' => 'all', 'position' => 1, 'filter' => 'all']);
        $clothing   = Category::create(['name' => 'Clothing', 'slug' => 'clothing', 'position' => 2]);
        $summer     = Category::create(['name' => 'Summer', 'slug' => 'summer', 'parent_id' => $clothing->id, 'position' => 3]);
        $sandals    = Category::create(['name' => 'Sandals', 'slug' => 'sandals', 'parent_id' => $summer->id, 'position' => 4]);
        $shirts     = Category::create(['name' => 'Shirts', 'slug' => 'shirts', 'parent_id' => $summer->id, 'position' => 5]);
        $tshirts    = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'parent_id' => $shirts->id, 'position' => 6]);
        $tanks      = Category::create(['name' => 'Tanks', 'slug' => 'tanks', 'parent_id' => $shirts->id, 'position' => 7]);
        $shorts     = Category::create(['name' => 'Shorts', 'slug' => 'shorts', 'parent_id' => $summer->id, 'position' => 8]);
        $winter     = Category::create(['name' => 'Winter', 'slug' => 'winter', 'parent_id' => $clothing->id, 'position' => 9]);
        $hats       = Category::create(['name' => 'Wool Hats', 'slug' => 'wool-hats', 'parent_id' => $winter->id, 'position' => 10]);
        $jackets    = Category::create(['name' => 'Jackets', 'slug' => 'jackets', 'parent_id' => $winter->id, 'position' => 11]);
    }

    public function seedProducts()
    {
        $adjectives = ['awesome', 'crappy', 'average', 'red', 'blue', 'green', 'white'];

        foreach ($adjectives as $adjective) {
            $shirt = Product::create([
                'name'          => ucfirst($adjective).' shirt',
                'slug'          => $adjective.'-shirt',
                'base_price'    => rand(5, 15),
            ]);
            $shirt->categories()->sync([rand(5, 7)]);

            $hat = Product::create([
                'name'          => ucfirst($adjective).' hat',
                'slug'          => $adjective.'-hat',
                'base_price'    => rand(5, 15),
            ]);
            $hat->categories()->sync([10]);

            $jacket = Product::create([
                'name'          => ucfirst($adjective).' jacket',
                'slug'          => $adjective.'-jacket',
                'base_price'    => rand(5, 15),
            ]);
            $hat->categories()->sync([11]);
        }

        Product::syncProducts(Product::all());
    }
}
