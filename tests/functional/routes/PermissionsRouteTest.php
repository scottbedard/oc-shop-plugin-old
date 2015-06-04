<?php namespace Bedard\Shop\Tests\Functional\Routes;

use Backend;
use Bedard\Shop\Tests\Fixtures\Auth;
use Bedard\Shop\Tests\Fixtures\Generate;

class PermissionsRouteTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * The following permissions tests simple create a user, and verify
     * that certain areas of the backend are either accessible or blocked.
     */
    public function test_product_permissions()
    {
        $user = Auth::createUser(['bedard.shop.access_products' => -1]);
        $this->call('GET', Backend::url('bedard/shop/products'));
        $this->assertResponseStatus(403);

        Auth::setPermissions($user, ['bedard.shop.access_products' => 1]);
        $this->call('GET', Backend::url('bedard/shop/products'));
        $this->assertResponseOk();
    }

    public function test_inventory_permissions()
    {
        $user = Auth::createUser(['bedard.shop.access_products' => 1, 'bedard.shop.access_inventories' => -1]);
        $product = Generate::product('Shirt');
        $response = $this->call('GET', Backend::url('bedard/shop/products/update/' . $product->id));
        $this->assertResponseOk();
        $this->assertFalse(strpos($response->getContent(), 'OPTIONSINVENTORIES_WIDGET'));

        Auth::setPermissions($user, ['bedard.shop.access_products' => 1, 'bedard.shop.access_inventories' => 1]);
        $response = $this->call('GET', Backend::url('bedard/shop/products/update/' . $product->id));
        $this->assertInternalType('int', strpos($response->getContent(), '<div id="options-inventories">'));
    }

    public function test_category_permissions()
    {
        $user = Auth::createUser(['bedard.shop.access_categories' => -1]);
        $this->call('GET', Backend::url('bedard/shop/categories'));
        $this->assertResponseStatus(403);

        Auth::setPermissions($user, ['bedard.shop.access_categories' => 1]);
        $this->call('GET', Backend::url('bedard/shop/categories'));
        $this->assertResponseOk();
    }

    public function test_discount_permissions()
    {
        $user = Auth::createUser(['bedard.shop.access_discounts' => -1]);
        $this->call('GET', Backend::url('bedard/shop/discounts'));
        $this->assertResponseStatus(403);

        Auth::setPermissions($user, ['bedard.shop.access_discounts' => 1]);
        $this->call('GET', Backend::url('bedard/shop/discounts'));
        $this->assertResponseOk();
    }
}
