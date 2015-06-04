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
            'name'        => 'bedard.shop::lang.general.plugin',
            'description' => 'bedard.shop::lang.general.description',
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
                'label'       => 'bedard.shop::lang.general.plugin',
                'url'         => Backend::url('bedard/shop/products'),
                'icon'        => 'icon-shopping-cart',
                'permissions' => ['bedard.shop.*'],
                'order'       => 300,

                'sideMenu' => [
                    'categories' => [
                        'label'         => 'bedard.shop::lang.categories.controller',
                        'icon'          => 'icon-folder-o',
                        'url'           => Backend::url('bedard/shop/categories'),
                        'permissions'   => ['bedard.shop.access_categories'],
                    ],
                    'products' => [
                        'label'         => 'bedard.shop::lang.products.controller',
                        'icon'          => 'icon-cubes',
                        'url'           => Backend::url('bedard/shop/products'),
                        'permissions'   => ['bedard.shop.access_products'],
                    ],
                    'discounts' => [
                        'label'         => 'bedard.shop::lang.discounts.controller',
                        'icon'          => 'icon-clock-o',
                        'url'           => Backend::url('bedard/shop/discounts'),
                        'permissions'   => ['bedard.shop.access_discounts'],
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
        return [
            'bedard.shop.access_categories' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_categories',
            ],
            'bedard.shop.access_products' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_products',
            ],
            'bedard.shop.access_inventories' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_inventories',
            ],
            'bedard.shop.access_discounts' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_discounts',
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
                'label' => 'Options & Inventories',
                'code'  => 'optionsinventories',
            ],
            'Bedard\Shop\FormWidgets\ValueManager' => [
                'label' => 'Value Manager',
                'code'  => 'valuemanager',
            ],
            'Bedard\Shop\FormWidgets\ValueSelector' => [
                'label' => 'Value Selector',
                'code'  => 'valueselector',
            ],
            'Owl\FormWidgets\Knob\Widget' => [
                'label' => 'Knob',
                'code'  => 'owl-knob'
            ],
        ];
    }
}
