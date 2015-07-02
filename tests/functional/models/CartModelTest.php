<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class CartModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_promotion_minimum_is_reached()
    {
        $manager    = new CartManager;
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
        $manager    = new CartManager;
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

    public function test_cart_savings_is_calculated_correctly()
    {
        $manager    = new CartManager;
        $product1   = Generate::product('Foo', ['base_price' => 100]);
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);
        $promotion  = Generate::promotion('Promo', ['is_cart_percentage' => false, 'cart_percentage' => 10, 'cart_exact' => 5]);

        $manager->add($inventory1->id);
        $manager->cart->loadRelationships();
        $this->assertEquals(0, $manager->cart->promotionSavings);

        $manager->applyPromotion('Promo');
        $manager->cart->isLoaded = false;
        $manager->cart->loadRelationships();
        $this->assertEquals(5, $manager->cart->promotionSavings);

        $manager->cart->promotion->cart_exact = 200;
        $manager->cart->promotion->save();
        $manager->cart->isLoaded = false;
        $manager->cart->loadRelationships();
        $this->assertEquals(100, $manager->cart->promotionSavings);

        $manager->cart->promotion->is_cart_percentage = true;
        $manager->cart->promotion->save();
        $manager->cart->isLoaded = false;
        $manager->cart->loadRelationships();
        $this->assertEquals(10, $manager->cart->promotionSavings);

        $manager->cart->promotion->end_at = Carbon::now()->subDays(1);
        $manager->cart->promotion->save();
        $manager->cart->isLoaded = false;
        $manager->cart->loadRelationships();
        $this->assertEquals(0, $manager->cart->promotionSavings);
    }

}
