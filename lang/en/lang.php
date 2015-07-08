<?php

return [

    //
    // General
    //
    'general' => [
        'plugin'                        => 'Shop',
        'description'                   => 'A feature rich ecommerce platform.',
    ],

    //
    // Components
    //
    'components' => [
        'cart' => [
            'description'               => 'Provides shopping cart functionality.',
            'name'                      => 'Cart',
        ],
        'category' => [
            'notfound'                  => 'Return 404 when not found',
            'notfound_description'      => 'Returns a 404 (page not found) response when the requested category does not exist.',
            'data'                      => 'Data',
            'default'                   => 'Default',
            'default_description'       => 'Default category to display when none is selected.',
            'description'               => 'Displays a category and it\'s products.',
            'load_description'          => 'Load full description',
            'load_description_info'     => 'Only enable if the category is displaying the full product descriptions.',
            'load_snippet'              => 'Load short description',
            'load_snippet_info'         => 'Only enable if the category is displaying the short product descriptions.',
            'gallery'                   => 'Load gallery images',
            'gallery_description'       => 'Only enable if the category is displaying product images.',
            'inventories'               => 'Load inventories',
            'inventories_description'   => 'Only enable if the category is displaying product inventory information.',
            'thumbnails'                => 'Load thumbnails',
            'thumbnails_description'    => 'Only enable if the category is displaying product thumbnails.',
            'name'                      => 'Category',
            'page'                      => 'Page number',
            'page_description'          => 'The page number to display. If the page doesn\'t exist, the closest existing page will be displayed.',
            'page_invalid'              => 'The page number must be a whole number greater than zero.',
            'slug'                      => 'Category',
            'slug_description'          => 'Select a category or enter a slug parameter.',
        ],
        'checkout' => [
            'default_country'           => 'Default country',
            'default_country_description' => 'This determines which country will be selected by default.',
            'default_country_none'      => 'No default country',
            'description'               => 'Processes checkout forms and payment.',
            'name'                      => 'Checkout',
        ],
        'navigation' => [
            'description'               => 'Displays a the category tree.',
            'name'                      => 'Navigation',
        ],
        'product' => [
            'description'               => 'Details for a shop product.',
            'name'                      => 'Product',
            'slug'                      => 'Product',
            'slug_description'          => 'Select a product or enter a slug parameter.',
            'use_selector'              => 'Use inventory selector',
            'use_selector_info'         => 'Includes the default inventory selection script. This enables a series of dropdowns to select a product\'s various options.',
        ],
    ],

    //
    // Form
    //
    'form' => [
        'delete_confirm'                => 'Are you sure?',
        'delete_confirm_name'           => 'Do you really want to delete this :name?',
        'delete_failed_name'            => 'Failed to delete :name.',
        'return_to_name_list'           => 'Return to :name list',
    ],

    //
    // Navigation
    //
    'navigation' => [
        'discounts'                     => 'Discounts',
        'categories'                    => 'Categories',
        'products'                      => 'Products',
        'promotions'                    => 'Promotions',
        'settings' => [
            'sidebar'                   => 'Settings',
            'currency'                  => 'Currency',
            'currency_description'      => 'Manager formatting and currency settings.',
            'general'                   => 'General Settings',
            'general_description'       => 'Manager general shop settings.',
            'payment'                   => 'Payments',
            'payment_description'       => 'Manage payment gateways and settings.',
            'shipping'                  => 'Shipping',
            'shipping_description'      => 'Manage shipping calculators and settings.',
        ],
        'shipping'                      => 'Shipping Table',
    ],

    //
    // Permissions
    //
    'permissions' => [
        'access_categories'             => 'Manage categories',
        'access_discounts'              => 'Manage discounts',
        'access_inventories'            => 'Manage inventories',
        'access_products'               => 'Manage products',
        'access_promotions'             => 'Manage promotions',
        'access_settings'               => 'Manage shop settings',
        'access_shipping_table'         => 'Manage shipping table'
    ],

    //
    // Common strings
    //
    'common' => [
        'created_at'                    => 'Created Date',
        'driver_saved'                  => 'Successfully saved :name settings.',
        'end_at'                        => 'End Date',
        'end_at_invalid'                => 'The end date must be after the start date.',
        'end_at_never'                  => 'Leave blank to run indefinitely.',
        'exact_amount'                  => 'Exact amount',
        'name'                          => 'Name',
        'percentage'                    => 'Percentage',
        'products_no_results'           => 'No products were found.',
        'products_none_selected'        => 'No products are selected.',
        'products_search'               => 'Enter product name...',
        'products_select'               => 'Select Products',
        'slug'                          => 'Slug',
        'start_at'                      => 'Start Date',
        'start_at_immediate'            => 'Leave blank to start immediately.',
        'status'                        => 'Status',
        'updated_at'                    => 'Last Updated',
        'weight_gr_abbreviated'         => 'g',
        'weight_gr_plural'              => 'Grams',
        'weight_gr_singular'            => 'Gram',
        'weight_kg_abbreviated'         => 'kg',
        'weight_kg_plural'              => 'Kilograms',
        'weight_kg_singular'            => 'Kilogram',
        'weight_oz_abbreviated'         => 'oz',
        'weight_oz_plural'              => 'Ounces',
        'weight_oz_singular'            => 'Ounce',
        'weight_lb_abbreviated'         => 'lb',
        'weight_lb_plural'              => 'Pounds',
        'weight_lb_singular'            => 'Pound',
    ],

    //
    // Resources
    //
    'categories' => [
        'behavior_tab'                  => 'Behavior',
        'children'                      => 'Children',
        'columns'                       => 'Columns',
        'controller'                    => 'Categories',
        'display_tab'                   => 'Display',
        'filter'                        => 'Filter',
        'filter_all'                    => 'All products',
        'filter_comment'                => '&#x2716; refers to the filter value.',
        'filter_created_less'           => 'Created in the last &#x2716; days',
        'filter_created_greater'        => 'Created more than &#x2716; days ago',
        'filter_discounted'             => 'Discounted products',
        'filter_price_less'             => 'Price less than &#x2716;',
        'filter_price_greater'          => 'Price creater than &#x2716;',
        'filter_tab'                    => 'Filters',
        'filter_value'                  => 'Value',
        'hide_out_of_stock'             => 'Hide out of stock products',
        'inherit_children'              => 'Product Inheritance',
        'inherit_children_off'          => 'Do not inherit child products',
        'inherit_children_on'           => 'Inherit child products',
        'invalid_parent'                => 'The parent category cannot be a descendent of this category.',
        'is_hidden'                     => 'Hide category from navigation',
        'list_title'                    => 'Manage Categories',
        'model'                         => 'Category',
        'none'                          => '<i>None</i>',
        'parent'                        => 'Parent',
        'parent_category'               => 'Parent Category',
        'reorder'                       => 'Re-order Categories',
        'reorder_empty'                 => 'There are no categories to re-order.',
        'reorder_success'               => 'Categories have been successfully re-ordered!',
        'rows'                          => 'Rows',
        'rows_display_all'              => 'Display all rows',
        'sort'                          => 'Product Order',
        'sort_comment'                  => 'Pagination is not available for randomly sorted categories.',
        'sort_date_asc'                 => 'Date created (Oldest first)',
        'sort_date_desc'                => 'Date created (Newest first)',
        'sort_name_asc'                 => 'Alphabetically (A-Z)',
        'sort_name_desc'                => 'Alphabetically (Z-A)',
        'sort_price_asc'                => 'Price (Lowest to highest)',
        'sort_price_desc'               => 'Price (Highest to lowest)',
        'sort_random'                   => 'Random',
        'sort_custom'                   => 'Custom',
    ],

    'currency' => [
        'code'                          => 'Code',
        'code_placeholder'              => 'Enter currency code (USD, EUR, GBP, etc...)',
        'decimal'                       => 'Decimal Character',
        'decimal_placeholder'           => '',
        'hide_double_zeros'             => 'Remove double zeros',
        'hide_double_zeros_comment'     => 'Example: "10.00" would be displayed as "10"',
        'symbol'                        => 'Symbol',
        'symbol_placeholder'            => 'Enter currency symbol ($, €, £, etc...)',
        'thousands'                     => 'Thousands Seperator',
        'thousands_placeholder'         => '',
    ],

    'discounts' => [
        'amount'                        => 'Amount',
        'amount_exact'                  => 'The exact amount to reduce prices by.',
        'amount_percentage'             => 'The percentage to reduce prices by.',
        'categories_no_results'         => 'No categories were found.',
        'categories_none_selected'      => 'No categories are selected.',
        'categories_search'             => 'Enter category name...',
        'categories_select'             => 'Select Categories',
        'controller'                    => 'Discounts',
        'hide_expired'                  => 'Hide expired',
        'list_title'                    => 'Manage Discounts',
        'method'                        => 'Discount Method',
        'model'                         => 'Discount',
        'status_expired'                => 'Expired',
        'status_running'                => 'Running',
        'status_upcoming'               => 'Upcoming',
    ],

    'drivers' => [
        'paypalexpress' => [
            'api_username'              => 'API username',
            'api_password'              => 'API password',
            'api_signature'             => 'API signature',
            'brand_name'                => 'Brand name',
            'border_color'              => 'Border color',
            'driver'                    => 'Paypal Express',
            'logo_url'                  => 'Logo URL',
            'production'                => 'Production',
            'sandbox'                   => 'Sandbox',
            'server'                    => 'Server',
            'tab_appearance'            => 'Appearance',
            'tab_connection'            => 'Connection',
        ],
        'basictable' => [
            'info'                      => 'There are no settings available for the basic shipping table.',
        ],
    ],

    'inventories' => [
        'default'                       => 'Default Inventory',
        'inventory_exists'              => 'That inventory already exists.',
        'model'                         => 'Inventory',
        'modifier'                      => 'Price modifier',
        'options_label'                 => 'Options',
        'options_placeholder'           => 'Select :name',
        'out_of_stock'                  => 'Out of stock',
        'quantity'                      => 'Quantity',
        'sku'                           => 'Stock keeping unit',
        'widget_label'                  => 'Inventories',
        'quantity_in_stock'             => ':quantity in stock',
    ],

    'options' => [
        'delete_text'                   => 'Related inventories will be deleted.',
        'model'                         => 'Option',
        'name_unique'                   => 'Option names must be unique.',
        'placeholder'                   => 'Placeholder',
        'update_success'                => 'Successfully updated option!',
        'update_failed'                 => 'Failed to update option.',
        'values'                        => 'Values',
        'values_required'               => 'Options must have at least one value.',
        'widget_label'                  => 'Options',
    ],

    'products' => [
        'base_price_min'                => 'The price must be greater than zero.',
        'base_price_numeric'            => 'The price must be a number.',
        'categories_empty'              => 'There are no categories available.',
        'controller'                    => 'Products',
        'description'                   => 'Description',
        'details_tab'                   => 'Details',
        'discount_ends'                 => 'Discount ends :date',
        'discount_indefinite'           => 'Discount runs indefinitely',
        'filter_disabled'               => 'Hide disabled',
        'filter_out_of_stock'           => 'Hide out of stock',
        'images'                        => 'Images',
        'images_tab'                    => 'Images',
        'in_stock'                      => ':quantity in stock',
        'is_enabled'                     => 'Product is enabled',
        'list_title'                    => 'Manage Products',
        'markdown_allowed'              => 'You may use markdown syntax.',
        'model'                         => 'Product',
        'name_placeholder'              => 'New product name',
        'options_inventories'           => 'Options & Inventories',
        'out_of_stock'                  => 'Out of stock',
        'price'                         => 'Price',
        'scoreboard_disabled'           => 'Disabled',
        'scoreboard_discounted'         => 'Discounted',
        'scoreboard_normal'             => 'Normal',
        'scoreboard_stock'              => 'In Stock',
        'scoreboard_stock_comment'      => ':instock of :total are in stock',
        'scoreboard_total'              => 'Total Products',
        'slug_placeholder'              => 'new-product-name',
        'snippet'                       => 'Short Description',
        'status_disabled'               => 'Disabled',
        'status_normal'                 => 'Normal',
        'thumbnails'                    => 'Thumbnails',
        'weight'                        => 'Weight',
        'weight_comment'                => 'Product weight in :units',
    ],

    'promotions' => [
        'cart_amount'                   => 'Cart discount',
        'cart_exact_comment'            => 'The exact amount to reduce the cart total by.',
        'cart_minimum'                  => 'Cart minimum',
        'cart_minimum_comment'          => 'Minimum cart balance required for promotion to apply.',
        'cart_method'                   => 'Cart discount method',
        'cart_percentage_comment'       => 'The percentage to reduce the cart total by.',
        'code'                          => 'Code',
        'controller'                    => 'Promotions',
        'countries_no_results'          => 'No countries were found.',
        'countries_none_selected'       => 'No countries are selected.',
        'countries_search'              => 'Enter country name...',
        'countries_select'              => 'Select Countries',
        'free_shipping'                 => 'Free Shipping',
        'list_title'                    => 'Manage Promotions',
        'message'                       => 'Message',
        'model'                         => 'Promotion',
        'required_products'             => 'Required products',
        'required_products_comment'     => 'If enabled, one or more required products must be purchased to utilize the promotion.',
        'shipping_amount'               => 'Shipping discount',
        'shipping_countries'            => 'Shipping restrictions',
        'shipping_countries_comment'    => 'If enabled, customers must reside in one of the given countries to utilize the shipping discount.',
        'shipping_exact_comment'        => 'Exact amount to reduce shipping cost by.',
        'shipping_method'               => 'Shipping discount method',
        'shipping_percentage_comment'   => 'Percentage to reduce shipping cost by.',
    ],

    'settings' => [
        'backend' => [
            'editor'                    => 'Backend editor',
            'editor_code'               => 'Code editor / markdown',
            'editor_rich'               => 'Rich text editor',
            'tab'                       => 'Backend',
            'weight_unit'               => 'Unit of weight',
        ],
        'cart' => [
            'life'                      => 'Cart Lifetime',
            'life_comment'              => 'This determines how long to keep carts alive after a user leaves the site.',
            'life_session'              => 'That session only',
            'life_half_day'             => '12 hours',
            'life_day'                  => '1 day',
            'life_week'                 => '1 week',
            'life_two_weeks'            => '2 weeks',
            'life_month'                => '1 month',
            'life_two_months'           => '2 months',
            'life_forever'              => 'Forever',
            'tab'                       => 'Shopping Carts',
        ],
        'payment' => [
            'driver_not_found'          => 'The payment gateway driver could not be found.',
            'default'                   => 'Default payment gateway',
            'default_placeholder'       => '-- select payment gateway --',
            'manage'                    => 'Manage payment gateways',
            'timing'                    => 'Reduce inventory',
            'timing_completed'          => 'When payment is received',
            'timing_immediate'          => 'When the payment process begins',
            'url_success'               => 'Success URL',
            'url_canceled'              => 'Canceled URL',
            'url_error'                 => 'Error URL',
        ],
        'shipping' => [
            'driver_not_found'          => 'The shipping driver could not be found.',
            'is_required'               => 'Calculator response',
            'is_required_on'            => 'Required, a response must be received to check out.',
            'is_required_off'           => 'Not required, a user may still checkout if calculator fails.',
            'calculator'                => 'Default shipping calculator',
            'calculator_placeholder'    => '-- select shipping calculator --',
            'manage'                    => 'Manage shipping calculators',
        ],
    ],

    'shippingmethods' => [
        'controller'                    => 'Shipping Methods',
        'list_title'                    => 'Manage Shipping Methods',
        'max_weight'                    => 'Maximum weight',
        'max_weight_comment'            => 'Maximum weight in :units (leave blank for no maximum)',
        'min_weight'                    => 'Minimum weight',
        'min_weight_comment'            => 'Minimum weight in :units (leave blank for no minimum)',
        'name'                          => 'Name',
        'model'                         => 'Shipping Method',
        'rates'                         => 'Shipping rates',
    ],

    'shippingrates' => [
        'base_price'                    => 'Base price',
        'base_price_comment'            => 'The shipping rate\'s starting price',
        'controller'                    => 'Shipping Rates',
        'countries'                     => 'Countries',
        'countries_comment'             => 'Select countries that this shipping rate applies to',
        'id'                            => 'ID',
        'model'                         => 'Shipping Rate',
        'rate'                          => 'Rate',
        'rate_comment'                  => 'Cost per :units',
        'states'                        => 'States',
        'states_comment'                => 'Leaving all states unselected will apply the rate to every state'
    ],

    'values' => [
        'delete_text'                   => 'Related inventories will be deleted upon saving.',
        'name_required'                 => 'All option values must be named.',
        'name_unique'                   => 'Value names must be unique.',
        'placeholder'                   => 'Type value and press "enter" or "tab"',
    ],
];
