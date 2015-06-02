<?php

return [

    //
    // General
    //
    'general' => [
        'plugin'                => 'Shop',
        'description'           => 'A feature rich ecommerce platform.',
    ],

    //
    // Permissions
    //
    'permissions' => [
        'access_categories'     => 'Manage categories',
        'access_products'       => 'Manage products',
        'access_inventories'    => 'Manage product options and inventories',
    ],

    //
    // Form
    //
    'form' => [
        'delete_confirm'        => 'Are you sure?',
        'delete_confirm_name'   => 'Do you really want to delete this :name?',
        'delete_failed_name'    => 'Failed to delete :name.',
        'return_to_name_list'   => 'Return to :name list',
    ],

    //
    // Common strings
    //
    'common' => [
        'name'                  => 'Name',
        'slug'                  => 'Slug',
    ],

    //
    // Resources
    //
    'categories' => [
        'children'              => 'Children',
        'controller'            => 'Categories',
        'inherit_children'      => 'Product Inheritance',
        'inherit_children_off'  => 'Do not inherit child products',
        'inherit_children_on'   => 'Inherit child products',
        'invalid_parent'        => 'The parent category cannot be a descendent of this category.',
        'list_title'            => 'Manage Categories',
        'model'                 => 'Category',
        'parent'                => 'Parent',
        'parent_category'       => 'Parent Category',
        'parent_empty'          => '<i>None</i>',
        'relationships'         => 'Relationships',
        'reorder'               => 'Re-order Categories',
        'reorder_empty'         => 'There are no categories to re-order.',
        'reorder_success'       => 'Categories have been successfully re-ordered!',
    ],

    'inventories' => [
        'default'               => 'Default Inventory',
        'model'                 => 'Inventory',
        'quantity'              => 'Quantity',
        'sku'                   => 'Stock Keeping Unit',
        'widget_label'          => 'Inventories',
    ],

    'products' => [
        'base_price_min'        => 'The price must be greater than zero.',
        'base_price_numeric'    => 'The price must be a number.',
        'categories_empty'      => 'There are no categories available.',
        'controller'            => 'Products',
        'description'           => 'Description',
        'details_tab'           => 'Details',
        'is_active'             => 'Product is active',
        'list_title'            => 'Manage Products',
        'markdown_allowed'      => 'You may use markdown syntax.',
        'model'                 => 'Product',
        'name_placeholder'      => 'New product name',
        'options_inventories'   => 'Options & Inventories',
        'price'                 => 'Price',
        'slug_placeholder'      => 'new-product-name',
        'status'                => 'Status',
        'status_normal'         => 'Normal',
        'status_disabled'       => 'Disabled',
    ],

    'options' => [
        'delete_text'           => 'Related inventories will be deleted.',
        'model'                 => 'Option',
        'name_unique'           => 'Option names must be unique.',
        'placeholder'           => 'Placeholder',
        'update_success'        => 'Successfully updated option!',
        'update_failed'         => 'Failed to update option.',
        'values'                => 'Values',
        'values_required'       => 'Options must have at least one value.',
        'widget_label'          => 'Options',
    ],

    'values' => [
        'delete_text'           => 'Related inventories will be deleted upon saving.',
        'name_required'         => 'All option values must be named.',
        'name_unique'           => 'Value names must be unique.',
        'placeholder'           => 'Type value and press "enter" or "tab"',
    ],
];
