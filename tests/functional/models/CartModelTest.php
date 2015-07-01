<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Tests\Fixtures\Generate;

class CartModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_promotion_minimum_is_reached()
    {
        $manager    = CartManager::openOrCreate();
        $promotion  = Generate::promotion('Foo');
        $product    = Generate::product('Bar', ['base_price' => 50]);
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);

        $manager->applyPromotion('Foo');
        $this->assertTrue($manager->cart->isPromotionMinimumReached);

        $manager->cart->promotion->cart_minimum = 20;
        $manager->cart->promotion->save();
        $manager->cart->isLoaded = false;
        $this->assertFalse($manager->cart->isPromotionMinimumReached);

        $manager->add($inventory->id);
        $manager->cart->isLoaded = false;
        $this->assertTrue($manager->cart->isPromotionMinimumReached);
    }

    public function test_cart_has_promotion_products()
    {
        $manager    = CartManager::openOrCreate();
        $product1   = Generate::product('Foo');
        $product2   = Generate::product('Bar');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);
        $promotion  = Generate::promotion('Promo');
        $promotion->products()->attach($product2->id);

        $manager->applyPromotion('Promo');
        $this->assertFalse($manager->cart->hasPromotionProducts);

        $manager->add($inventory2->id);
        $item = CartItem::where('cart_id', $manager->cart->id)->where('inventory_id', $inventory2->id)->first();
        $manager->cart->load('items');
        $this->assertTrue($manager->cart->hasPromotionProducts);

        $manager->add($inventory1->id);
        $manager->cart->load('items');
        $this->assertTrue($manager->cart->hasPromotionProducts);

        $manager->remove($item->id);
        $manager->cart->load('items');
        $this->assertFalse($manager->cart->hasPromotionProducts);
    }

}
