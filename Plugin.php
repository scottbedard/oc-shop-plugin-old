<?php namespace Bedard\Shop;

use Backend;
use Lang;
use System\Classes\PluginBase;

/**
 * Shop Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return  array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Shop',
            'description' => 'A feature rich ecommerce platform.',
            'author'      => 'Scott Bedard',
            'icon'        => 'icon-shopping-cart'
        ];
    }

    /**
     * Register plugin navigation
     *
     * @return  array
     */
    public function registerNavigation()
    {
        return [
            'shop' => [
                'label'       => 'Shop',
                'url'         => Backend::url('bedard/shop/products'),
                'icon'        => 'icon-shopping-cart',
                'permissions' => ['bedard.shop.*'],
                'order'       => 300,

                'sideMenu' => [
                    'categories' => [
                        'label'         => Lang::get('bedard.shop::lang.categories.controller'),
                        'icon'          => 'icon-folder-o',
                        'url'           => Backend::url('bedard/shop/categories'),
                        'permissions'   => ['bedard.shop.access_categories'],
                    ],
                    'products' => [
                        'label'         => Lang::get('bedard.shop::lang.products.controller'),
                        'icon'          => 'icon-cubes',
                        'url'           => Backend::url('bedard/shop/products'),
                        'permissions'   => ['bedard.shop.access_products'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Register user permissions
     *
     * @return  array
     */
    public function registerPermissions()
    {
        $shop = Lang::get('bedard.shop::lang.permissions.tab');

        return [
            'bedard.shop.access_categories' => [
                'tab'   => $shop,
                'label' => Lang::get('bedard.shop::lang.permissions.access_categories'),
            ],
            'bedard.shop.access_products' => [
                'tab'   => $shop,
                'label' => Lang::get('bedard.shop::lang.permissions.access_products'),
            ],
        ];
    }

    /**
     *  Register form widgets
     *
     * @return  array
     */
    public function registerFormWidgets()
    {
        return [
            'Bedard\Shop\FormWidgets\OptionsInventories' => [
                'label' => 'Options And Inventories',
                'code'  => 'options-inventories'
            ],
        ];
    }
}
