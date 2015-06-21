<?php namespace Bedard\Shop\Updates;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;
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

    /**
     * Seed demo categories
     */
    public function seedCategories()
    {
        $categories = [
            ['name' => 'All', 'filter' => 'all'],
            ['name' => 'New this week', 'filter' => 'created_less', 'filter_value'  => 7],
            ['name' => 'Clothing'],
            ['name' => 'Summer', 'parent' => 'clothing'],
            ['name' => 'T-Shirts', 'parent' => 'summer'],
            ['name' => 'Shorts', 'parent' => 'summer'],
            ['name' => 'Winter', 'parent' => 'clothing'],
            ['name' => 'Jackets', 'parent' => 'winter'],
            ['name' => 'Hats', 'parent' => 'clothing'],
            ['name' => 'Electronics'],
            ['name' => 'Televisions', 'parent' => 'electronics'],
            ['name' => 'Cell Phones', 'parent' => 'electronics'],
            ['name' => 'Tablets', 'parent' => 'electronics'],
            ['name' => 'Computers', 'parent' => 'electronics'],
            ['name' => 'Desktops', 'parent' => 'computers'],
            ['name' => 'Laptops', 'parent' => 'computers'],
            ['name' => '$50 and under', 'filter' => 'price_less', 'filter_value' => 50],
            ['name' => 'On Sale', 'filter' => 'discounted'],
            ['name' => 'Featured', 'filter' => 'all', 'sort' => 'random', 'rows' => 1, 'columns' => 3],
        ];

        foreach ($categories as $i => $category) {
            $parent_id = null;
            if (isset($category['parent']) && ($parent = Category::where('slug', $category['parent'])->first())) {
                $parent_id = $parent->id;
            }

            $seed = Generate::category($category['name'], [
                'parent_id'     => $parent_id,
                'position'      => $i,
                'filter'        => isset($category['filter']) ? $category['filter'] : null,
                'filter_value'  => isset($category['filter_value']) ? $category['filter_value'] : 0,
                'rows'          => isset($category['rows']) ? $category['rows'] : 3,
                'columns'       => isset($category['columns']) ? $category['columns'] : 4,
                'sort'          => isset($category['sort']) ? $category['sort'] : 'created_at-desc',
            ]);
        }
    }

    /**
     * Seed demo products, options, values, and inventories
     */
    public function seedProducts()
    {
        $faker = \Faker\Factory::create();
        $is_active = 6;

        // Create some clothing...
        $shirts = [
            ['name' => 'Pocket Tee', 'base_price' => 20, 'categories' => ['t-shirts']],
            ['name' => 'Tropical Tee', 'base_price' => 20, 'categories' => ['t-shirts']],
            ['name' => 'Be Easy Tee', 'base_price' => 16, 'categories' => ['t-shirts']],
            ['name' => 'Tie Die Tee', 'base_price' => 20, 'categories' => ['t-shirts']],
            ['name' => 'Basketball Tee', 'base_price' => 18, 'categories' => ['t-shirts']],
            ['name' => 'Khaki Shorts', 'base_price' => 24, 'categories' => ['shorts']],
            ['name' => 'Jean Shorts', 'base_price' => 20, 'categories' => ['shorts']],
            ['name' => 'Heavy Jacket', 'base_price' => 45, 'categories' => ['jackets']],
            ['name' => 'Light Jacket', 'base_price' => 40, 'categories' => ['jackets']],
            ['name' => 'Windbreaker', 'base_price' => 40, 'categories' => ['jackets']],
            ['name' => 'Bucket Cap', 'base_price' => 20, 'categories' => ['hats']],
            ['name' => 'Snap Back', 'base_price' => 20, 'categories' => ['hats']],
        ];
        foreach ($shirts as $shirt) {
            $seed = Generate::product($shirt['name'], [
                'base_price'    => $shirt['base_price'],
                'description'   => $faker->paragraph(10),
                'snippet'       => $faker->paragraph(2),
                'is_active'     => (bool) rand(0, $is_active),
            ]);

            $seed->categories()->sync(Category::whereIn('slug', $shirt['categories'])->lists('id'));

            $size = Generate::option('Size', ['product_id' => $seed->id]);
            $small  = Generate::value($size, 'Small', ['position' => 0]);
            $medium = Generate::value($size, 'Medium', ['position' => 1]);
            $large  = Generate::value($size, 'Large', ['position' => 2]);
            $inv1   = Generate::inventory($seed, [$small->id], ['quantity' => rand(0, 2)]);
            $inv2   = Generate::inventory($seed, [$medium->id], ['quantity' => rand(0, 2)]);
            $inv3   = Generate::inventory($seed, [$large->id], ['quantity' => rand(0, 2)]);
        }

        // Create some electronics...
        $tvs = [
            ['name' => 'LG 60" Plasma Screen', 'base_price' => 749.99, 'categories' => ['televisions']],
            ['name' => 'LG 50" Plasma Screen', 'base_price' => 699.99, 'categories' => ['televisions']],
            ['name' => 'Samsung 40" Smart TV', 'base_price' => 479.99, 'categories' => ['televisions']],
            ['name' => 'Samsung 32" TV', 'base_price' => 349.99, 'categories' => ['televisions']],
            ['name' => 'iPad Air', 'base_price' => 499.99, 'categories' => ['tablets']],
            ['name' => 'iPad Mini 16', 'base_price' => 299.99, 'categories' => ['tablets']],
            ['name' => 'Amazon Kindle Paperwhite', 'base_price' => 119.99, 'categories' => ['tablets']],
            ['name' => 'Apple iPhone 6', 'base_price' => 699.99, 'categories' => ['cell-phones']],
            ['name' => 'Apple iPhone 6 Plus', 'base_price' => 799.99, 'categories' => ['cell-phones']],
            ['name' => 'Samsung Galexy S5', 'base_price' => 339.99, 'categories' => ['cell-phones']],
            ['name' => 'Samsung Galexy Note 3', 'base_price' => 229.99, 'categories' => ['cell-phones']],
        ];
        foreach ($tvs as $tv) {
            $seed = Generate::product($tv['name'], [
                'base_price'    => $tv['base_price'],
                'description'   => $faker->paragraph(10),
                'snippet'       => $faker->paragraph(2),
                'is_active'     => (bool) rand(0, $is_active),
            ]);
            $seed->categories()->sync(Category::whereIn('slug', $tv['categories'])->lists('id'));

            $inventory = Generate::inventory($seed, [], ['quantity' => rand(0, 5)]);
        }

        // Create some computers
        $computers = [
            ['name' => 'Apple iMac', 'base_price' => 399, 'options' => ['500GB' => 0, '750GB' => 100, '1TB' => 200], 'categories' => ['desktops']],
            ['name' => 'Dell Insipiron', 'base_price' => 499, 'options' => ['500GB' => 0, '750GB' => 100, '1TB' => 200], 'categories' => ['desktops']],
            ['name' => 'HP Pavilion', 'base_price' => 699, 'options' => ['500GB' => 0, '750GB' => 100, '1TB' => 200], 'categories' => ['desktops']],
            ['name' => 'Apple MacBook Pro', 'base_price' => 1299, 'options' => ['500GB' => 0, '750GB' => 100, '1TB' => 200], 'categories' => ['laptops']],
        ];
        foreach ($computers as $computer) {
            $seed = Generate::product($computer['name'], [
                'base_price' => $computer['base_price'],
                'description' => $faker->paragraph(10),
                'is_active' => (bool) rand(0, $is_active),
            ]);
            $seed->categories()->sync(Category::whereIn('slug', $computer['categories'])->lists('id'));

            $hd = Generate::option('Hard Drive', ['product_id' => $seed->id]);
            foreach ($computer['options'] as $option => $modifier) {
                $option = Generate::value($hd, $option);
                $inventory = Generate::inventory($seed, [$option->id], ['quantity' => rand(0, 2), 'modifier' => $modifier]);
            }
        }
    }

    public function seedDiscounts()
    {
        // Make an expired discount
        $tshirts = Category::where('slug', 't-shirts')->first();
        $discount = Generate::discount('T-Shirt sale!', [
            'amount_percentage' => 25,
            'is_percentage'     => true,
            'end_at'            => Carbon::yesterday(),
        ]);
        $discount->categories()->add($tshirts);
        $discount->load('categories');
        $discount->syncProducts();

        // Make an upcoming discount
        $tablets = Category::where('slug', 'tablets')->first();
        $discount = Generate::discount('Tablet clearance!', [
            'amount_exact'  => 25,
            'is_percentage' => false,
            'start_at'      => Carbon::today()->addDay(3),
        ]);
        $discount->categories()->add($tablets);
        $discount->load('categories');
        $discount->syncProducts();

        // Make a current discount
        $clothing = Category::where('slug', 'clothing')->first();
        $discount = Generate::discount('Clothing sale!', [
            'amount_percentage' => 15,
            'is_percentage'     => true,
        ]);
        $discount->categories()->add($clothing);
        $discount->load('categories');
        $discount->syncProducts();
    }
}
