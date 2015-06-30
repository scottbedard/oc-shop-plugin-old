<?php namespace Bedard\Shop;

use Backend;
use Bedard\Shop\Models\Currency;
use Lang;
use System\Classes\PluginBase;

/**
 * Shop Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.Location'];

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
                    'promotions' => [
                        'label'         => 'bedard.shop::lang.promotions.controller',
                        'icon'          => 'icon-star',
                        'url'           => Backend::url('bedard/shop/promotions'),
                        'permissions'   => ['bedard.shop.access_promotions'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Register settings pages
     *
     * @return  array
     */
    public function registerSettings()
    {
        // If the currency is in the font-awesome stack, use it as the currency settings icon.
        $code = Currency::getCode();
        $fontAwesome = ['BTC', 'ILS', 'KRW', 'TRY', 'EUR', 'INR', 'USD', 'GBP', 'JPY', 'RUB'];
        $currencyIcon = $code && in_array($code, $fontAwesome)
            ? 'icon-'.strtolower($code)
            : 'icon-money';

        return [
            'settings' => [
                'label'         => 'bedard.shop::lang.settings.model',
                'description'   => 'bedard.shop::lang.settings.description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\Settings',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => 'icon-cog',
                'order'         => 100,
            ],
            'currency' => [
                'label'         => 'bedard.shop::lang.currency.model',
                'description'   => 'bedard.shop::lang.currency.description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\Currency',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => $currencyIcon,
                'order'         => 200,
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
            'bedard.shop.access_promotions' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_promotions',
            ],
            'bedard.shop.access_settings' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_settings',
            ],
        ];
    }

    /**
     * Register form widgets
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
            'Bedard\Shop\FormWidgets\RelationSelector' => [
                'labek' => 'Relation Selector',
                'code'  => 'relationselector',
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

    /**
     * Register components
     *
     * @return  array
     */
    public function registerComponents()
    {
        return [
            'Bedard\Shop\Components\Cart'       => 'shopCart',
            'Bedard\Shop\Components\Category'   => 'shopCategory',
            'Bedard\Shop\Components\Navigation' => 'shopNavigation',
            'Bedard\Shop\Components\Product'    => 'shopProduct',
        ];
    }

    /**
     * Register Twig extensions
     *
     * @return  array
     */
    public function registerMarkupTags()
    {
        return [
             'filters' => [
                'moneyFormat' => [$this, 'moneyFormat']
            ],
        ];
    }

    /**
     * MoneyFormat twig extension
     *
     * @param   string  $text
     * @return  string
     */
    public function moneyFormat($text)
    {
        return Currency::format($text);
    }
}
