<?php

return [

    //
    // General
    //
    'general' => [
        'plugin'                    => 'Shop',
        'description'               => 'A feature rich ecommerce platform.',
    ],

    //
    // Permissions
    //
    'permissions' => [
        'access_categories'         => 'Manage categories',
        'access_products'           => 'Manage products',
        'access_inventories'        => 'Manage inventories',
        'access_discounts'          => 'Manage discounts',
        'access_settings'           => 'Manage shop settings',
    ],

    //
    // Form
    //
    'form' => [
        'delete_confirm'            => 'Are you sure?',
        'delete_confirm_name'       => 'Do you really want to delete this :name?',
        'delete_failed_name'        => 'Failed to delete :name.',
        'return_to_name_list'       => 'Return to :name list',
    ],

    //
    // Common strings
    //
    'common' => [
        'name'                      => 'Name',
        'slug'                      => 'Slug',
        'status'                    => 'Status',
    ],

    //
    // Resources
    //
    'categories' => [
        'children'                  => 'Children',
        'controller'                => 'Categories',
        'display_tab'               => 'Display',
        'hide_out_of_stock'         => 'Hide out of stock products',
        'inherit_children'          => 'Product Inheritance',
        'inherit_children_off'      => 'Do not inherit child products',
        'inherit_children_on'       => 'Inherit child products',
        'invalid_parent'            => 'The parent category cannot be a descendent of this category.',
        'list_title'                => 'Manage Categories',
        'model'                     => 'Category',
        'parent'                    => 'Parent',
        'parent_category'           => 'Parent Category',
        'parent_empty'              => '<i>None</i>',
        'relationships_tab'         => 'Relationships',
        'reorder'                   => 'Re-order Categories',
        'reorder_empty'             => 'There are no categories to re-order.',
        'reorder_success'           => 'Categories have been successfully re-ordered!',
        'sort_order'                => 'Product Order',
        'sort_date_asc'             => 'Date added (Oldest first)',
        'sort_date_desc'            => 'Date added (Newest first)',
        'sort_name_asc'             => 'Alphabetically (A-Z)',
        'sort_name_desc'            => 'Alphabetically (Z-A)',
        'sort_price_asc'            => 'Price (Lowest to highest)',
        'sort_price_desc'           => 'Price (Highest to lowest)',
        'sort_custom'               => 'Custom',
    ],

    'currency' => [
        'code'                      => 'Code',
        'code_placeholder'          => 'Enter currency code (USD, EUR, GBP, etc...)',
        'decimal'                   => 'Decimal Character',
        'decimal_placeholder'       => '',
        'description'               => 'Manage currency and formatting.',
        'model'                     => 'Currency',
        'symbol'                    => 'Symbol',
        'symbol_placeholder'        => 'Enter currency symbol ($, €, £, etc...)',
        'thousands'                 => 'Thousands Seperator',
        'thousands_placeholder'     => '',
    ],

    'discounts' => [
        'amount'                    => 'Amount',
        'amount_exact'              => 'The exact amount to reduce prices by.',
        'amount_percentage'         => 'The percentage to reduce prices by.',
        'controller'                => 'Discounts',
        'end_at'                    => 'End Date',
        'end_at_invalid'            => 'The end date must be after the start date.',
        'hide_expired'              => 'Hide expired',
        'list_title'                => 'Manage Discounts',
        'method'                    => 'Discount Method',
        'method_exact'              => 'Exact Amount',
        'method_percentage'         => 'Percentage',
        'model'                     => 'Discount',
        'no_end'                    => 'Leave blank to run indefinitely.',
        'no_start'                  => 'Leave blank to start immediately.',
        'start_at'                  => 'Start Date',
        'status_expired'            => 'Expired',
        'status_running'            => 'Running',
        'status_upcoming'           => 'Upcoming',
    ],

    'inventories' => [
        'default'                   => 'Default Inventory',
        'inventory_exists'          => 'That inventory already exists.',
        'model'                     => 'Inventory',
        'modifier'                  => 'Price modifier',
        'options_label'             => 'Options',
        'options_placeholder'       => 'Select :name',
        'out_of_stock'              => 'Out of stock',
        'quantity'                  => 'Quantity',
        'sku'                       => 'Stock keeping unit',
        'widget_label'              => 'Inventories',
        'quantity_in_stock'         => ':quantity in stock',
    ],

    'products' => [
        'base_price_min'            => 'The price must be greater than zero.',
        'base_price_numeric'        => 'The price must be a number.',
        'categories_empty'          => 'There are no categories available.',
        'controller'                => 'Products',
        'description'               => 'Description',
        'details_tab'               => 'Details',
        'discount_ends'             => 'Discount ends :date',
        'discount_indefinite'       => 'Discount runs indefinitely',
        'filter_disabled'           => 'Hide disabled',
        'filter_out_of_stock'       => 'Hide out of stock',
        'images'                    => 'Images',
        'images_tab'                => 'Images',
        'in_stock'                  => ':quantity in stock',
        'is_active'                 => 'Product is active',
        'list_title'                => 'Manage Products',
        'markdown_allowed'          => 'You may use markdown syntax.',
        'model'                     => 'Product',
        'name_placeholder'          => 'New product name',
        'options_inventories'       => 'Options & Inventories',
        'options_inventories_hint'  => 'Please save the product before creating options or inventories.',
        'out_of_stock'              => 'Out of stock',
        'price'                     => 'Price',
        'scoreboard_disabled'       => 'Disabled',
        'scoreboard_discounted'     => 'Discounted',
        'scoreboard_stock'          => 'In Stock',
        'scoreboard_stock_comment'  => ':instock of :total are in stock',
        'scoreboard_normal'         => 'Normal',
        'scoreboard_total'          => 'Total Products',
        'slug_placeholder'          => 'new-product-name',
        'status_normal'             => 'Normal',
        'status_disabled'           => 'Disabled',
        'thumbnails'                => 'Thumbnails',
    ],

    'options' => [
        'delete_text'               => 'Related inventories will be deleted.',
        'model'                     => 'Option',
        'name_unique'               => 'Option names must be unique.',
        'placeholder'               => 'Placeholder',
        'update_success'            => 'Successfully updated option!',
        'update_failed'             => 'Failed to update option.',
        'values'                    => 'Values',
        'values_required'           => 'Options must have at least one value.',
        'widget_label'              => 'Options',
    ],

    'values' => [
        'delete_text'               => 'Related inventories will be deleted upon saving.',
        'name_required'             => 'All option values must be named.',
        'name_unique'               => 'Value names must be unique.',
        'placeholder'               => 'Type value and press "enter" or "tab"',
    ],
];
