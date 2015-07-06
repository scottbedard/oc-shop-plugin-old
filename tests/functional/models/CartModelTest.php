<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\ShippingSettings;
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
        $manager->loadItemData(true);
        $this->assertFalse($manager->cart->isPromotionMinimumReached);

        $manager->addItem($inventory->id);
        $manager->loadItemData(true);
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

        $manager->addItem($inventory2->id);
        $item = CartItem::where('cart_id', $manager->cart->id)->where('inventory_id', $inventory2->id)->first();
        $manager->cart->load('items');
        $this->assertTrue($manager->cart->hasPromotionProducts);

        $manager->addItem($inventory1->id);
        $manager->cart->load('items');
        $this->assertTrue($manager->cart->hasPromotionProducts);

        $manager->removeItems($item->id);
        $manager->cart->load('items');
        $this->assertFalse($manager->cart->hasPromotionProducts);
    }

    public function test_cart_savings_is_calculated_correctly()
    {
        $manager    = new CartManager;
        $product1   = Generate::product('Foo', ['base_price' => 100]);
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);
        $promotion  = Generate::promotion('Promo', ['is_cart_percentage' => false, 'cart_percentage' => 10, 'cart_exact' => 5]);

        $manager->addItem($inventory1->id);
        $manager->loadItemData(true);
        $this->assertEquals(0, $manager->cart->promotionSavings);

        $manager->applyPromotion('Promo');
        $manager->loadItemData(true);
        $this->assertEquals(5, $manager->cart->promotionSavings);

        $manager->cart->promotion->cart_exact = 200;
        $manager->cart->promotion->save();
        $manager->loadItemData(true);
        $this->assertEquals(100, $manager->cart->promotionSavings);

        $manager->cart->promotion->is_cart_percentage = true;
        $manager->cart->promotion->save();
        $manager->loadItemData(true);
        $this->assertEquals(10, $manager->cart->promotionSavings);

        $manager->cart->promotion->end_at = Carbon::now()->subDays(1);
        $manager->cart->promotion->save();
        $manager->loadItemData(true);
        $this->assertEquals(0, $manager->cart->promotionSavings);
    }

    public function test_shipping_is_required()
    {
        $cart = Generate::cart();
        $cart = Cart::find($cart->id);

        ShippingSettings::set('behavior', 'off');
        $this->assertFalse($cart->shippingIsRequired);

        ShippingSettings::set('behavior', 'on');
        $this->assertTrue($cart->shippingIsRequired);

        $cart->shipping_failed = true;
        $this->assertFalse($cart->shippingIsRequired);

        ShippingSettings::set('behavior', 'required');
        $this->assertTrue($cart->shippingIsRequired);

        $cart->shipping_rates = [
            ['name' => 'Standard', 'cost' => 2],
            ['name' => 'Priority', 'cost' => 5],
        ];
        $cart->shipping_failed = false;
        $this->assertFalse($cart->shippingIsRequired);

        ShippingSettings::set('behavior', 'on');
        $this->assertFalse($cart->shippingIsRequired);
    }
}
