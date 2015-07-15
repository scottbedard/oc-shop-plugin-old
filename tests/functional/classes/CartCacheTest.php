<?php namespace Bedard\Shop\Tests\Functional\Classes;

use Bedard\Shop\Classes\CartCache;
use Bedard\Shop\Tests\Fixtures\Generate;
use Illuminate\Database\Eloquent\Collection;

class CartCacheTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_cart_caching()
    {
        //
        // todo
        //

        // $product    = Generate::product('Bar');
        // $size       = Generate::option('Size');
        // $small      = Generate::value($size, 'Small');
        // $large      = Generate::value($size, 'Large');
        // $inventory  = Generate::inventory($product, [$small->id]);
        //
        // $product->load('inventories.values.option');
        //
        // $cart = Generate::cart();
        // $cartItem = Generate::cartItem($cart, $inventory);
        //
        // $cache = new CartCache;
        // $result = $cache->cache($cart);
    }
}
