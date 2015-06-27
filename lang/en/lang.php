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
    // Permissions
    //
    'permissions' => [
        'access_categories'             => 'Manage categories',
        'access_discounts'              => 'Manage discounts',
        'access_inventories'            => 'Manage inventories',
        'access_products'               => 'Manage products',
        'access_promotions'             => 'Manage promotions',
        'access_settings'               => 'Manage shop settings',
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
    // Common strings
    //
    'common' => [
        'exact_amount'                  => 'Exact amount',
        'name'                          => 'Name',
        'percentage'                    => 'Percentage',
        'slug'                          => 'Slug',
        'status'                        => 'Status',
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
        'description'                   => 'Manage currency and formatting.',
        'hide_double_zeros'             => 'Remove double zeros',
        'hide_double_zeros_comment'     => 'Example: "10.00" would be displayed as "10"',
        'model'                         => 'Currency',
        'symbol'                        => 'Symbol',
        'symbol_placeholder'            => 'Enter currency symbol ($, €, £, etc...)',
        'thousands'                     => 'Thousands Seperator',
        'thousands_placeholder'         => '',
    ],

    'discounts' => [
        'amount'                        => 'Amount',
        'amount_exact'                  => 'The exact amount to reduce prices by.',
        'amount_percentage'             => 'The percentage to reduce prices by.',
        'controller'                    => 'Discounts',
        'end_at'                        => 'End Date',
        'end_at_invalid'                => 'The end date must be after the start date.',
        'hide_expired'                  => 'Hide expired',
        'list_title'                    => 'Manage Discounts',
        'method'                        => 'Discount Method',
        'model'                         => 'Discount',
        'no_end'                        => 'Leave blank to run indefinitely.',
        'no_start'                      => 'Leave blank to start immediately.',
        'start_at'                      => 'Start Date',
        'status_expired'                => 'Expired',
        'status_running'                => 'Running',
        'status_upcoming'               => 'Upcoming',
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
        'is_active'                     => 'Product is active',
        'list_title'                    => 'Manage Products',
        'markdown_allowed'              => 'You may use markdown syntax.',
        'model'                         => 'Product',
        'name_placeholder'              => 'New product name',
        'options_inventories'           => 'Options & Inventories',
        'options_inventories_hint'      => 'Please save the product before creating options or inventories.',
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
    ],

    'promotions' => [
        'cart_amount'                   => 'Cart discount',
        'cart_exact_comment'            => 'The exact amount to reduce the cart total by.',
        'cart_method'                   => 'Cart discount method',
        'cart_percentage_comment'       => 'The percentage to reduce the cart total by.',
        'code'                          => 'Code',
        'controller'                    => 'Promotions',
        'list_title'                    => 'Manage Promotions',
        'message'                       => 'Message',
        'model'                         => 'Promotion',
        'required_products'             => 'Required products',
        'shipping_amount'               => 'Shipping discount',
        'shipping_exact_comment'        => 'Exact amount to reduce shipping cost by.',
        'shipping_method'               => 'Shipping discount method',
        'shipping_percentage_comment'   => 'Percentage to reduce shipping cost by.',
    ],

    'settings' => [
        'backend' => [
            'editor'                    => 'Editor',
            'editor_code'               => 'Code editor / markdown',
            'editor_rich'               => 'Rich text editor',
            'tab'                       => 'Backend',
        ],
        'cart' => [
            'life'                      => 'Time to live',
            'life_description'          => 'This defines how long a cart should "stay alive" after the user leaves the page.',
            'life_half_day'             => '12 hours',
            'life_day'                  => '1 day',
            'life_week'                 => '1 week',
            'life_two_weeks'            => '2 weeks',
            'life_month'                => '1 month',
            'life_forever'              => 'Forever',
            'tab'                       => 'Shopping Carts',
        ],
        'description'                   => 'Manage general shop settings.',
        'model'                         => 'Settings',
    ],

    'values' => [
        'delete_text'                   => 'Related inventories will be deleted upon saving.',
        'name_required'                 => 'All option values must be named.',
        'name_unique'                   => 'Value names must be unique.',
        'placeholder'                   => 'Type value and press "enter" or "tab"',
    ],
];
