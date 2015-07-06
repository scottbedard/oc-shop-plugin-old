<?php namespace Bedard\Shop;

use App;
use Backend;
use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Classes\CurrencyHelper;
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
     * Plugin startup
     */
    public function boot()
    {
        // Register the CartManager as a singleton
        App::singleton('Bedard\Shop\Classes\CartManager', function() {
            return new CartManager;
        });
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
                        'label'         => 'bedard.shop::lang.navigation.categories',
                        'icon'          => 'icon-folder-o',
                        'url'           => Backend::url('bedard/shop/categories'),
                        'permissions'   => ['bedard.shop.access_categories'],
                    ],
                    'products' => [
                        'label'         => 'bedard.shop::lang.navigation.products',
                        'icon'          => 'icon-cubes',
                        'url'           => Backend::url('bedard/shop/products'),
                        'permissions'   => ['bedard.shop.access_products'],
                    ],
                    'discounts' => [
                        'label'         => 'bedard.shop::lang.navigation.discounts',
                        'icon'          => 'icon-clock-o',
                        'url'           => Backend::url('bedard/shop/discounts'),
                        'permissions'   => ['bedard.shop.access_discounts'],
                    ],
                    'promotions' => [
                        'label'         => 'bedard.shop::lang.navigation.promotions',
                        'icon'          => 'icon-star',
                        'url'           => Backend::url('bedard/shop/promotions'),
                        'permissions'   => ['bedard.shop.access_promotions'],
                    ],
                    'shipping' => [
                        'label'         => 'bedard.shop::lang.navigation.shipping',
                        'icon'          => 'icon-table',
                        'url'           => Backend::url('bedard/shop/shippingmethods'),
                        'permissions'   => ['bedard.shop.access_shipping_table'],
                    ],
                    'settings' => [
                        'label'         => 'bedard.shop::lang.navigation.settings.sidebar',
                        'icon'          => 'icon-cog',
                        'url'           => Backend::url('system/settings/update/bedard/shop/settings'),
                        'permissions'   => ['bedard.shop.access_settings'],
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
                'label'         => 'bedard.shop::lang.navigation.settings.general',
                'description'   => 'bedard.shop::lang.navigation.settings.general_description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\Settings',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => 'icon-cog',
                'order'         => 100,
            ],
            'currency' => [
                'label'         => 'bedard.shop::lang.navigation.settings.currency',
                'description'   => 'bedard.shop::lang.navigation.settings.currency_description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\Currency',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => $currencyIcon,
                'order'         => 200,
            ],
            'shipping' => [
                'label'         => 'bedard.shop::lang.navigation.settings.shipping',
                'description'   => 'bedard.shop::lang.navigation.settings.shipping_description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\ShippingSettings',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => 'icon-truck',
                'order'         => 300,
            ],
            'payment' => [
                'label'         => 'bedard.shop::lang.navigation.settings.payment',
                'description'   => 'bedard.shop::lang.navigation.settings.payment_description',
                'category'      => 'bedard.shop::lang.general.plugin',
                'class'         => 'Bedard\Shop\Models\PaymentSettings',
                'permissions'   => ['bedard.shop.access_settings'],
                'icon'          => 'icon-credit-card',
                'order'         => 400,
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
            'bedard.shop.access_shipping_table' => [
                'tab'   => 'bedard.shop::lang.general.plugin',
                'label' => 'bedard.shop::lang.permissions.access_shipping_table',
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
            'Bedard\Shop\Components\Checkout'   => 'shopCheckout',
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
        return CurrencyHelper::format($text);
    }
}
