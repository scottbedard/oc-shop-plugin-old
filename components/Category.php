<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Models\Category as CategoryModel;
use Cms\Classes\ComponentBase;
use Lang;

class Category extends ComponentBase
{

    /**
     * @var CategoryModel   The category being viewed
     */
    public $category;

    /**
     * @var Collection      The category's products
     */
    public $products;

    /**
     * @var array           Pagination data (keys: first, last, current, previous, next)
     */
    public $pagination = [];

    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'bedard.shop::lang.components.category.name',
            'description'   => 'bedard.shop::lang.components.category.description'
        ];
    }

    /**
     * Define properties
     *
     * @return  array
     */
    public function defineProperties()
    {
        return [
            'slug' => [
                'title'             => 'bedard.shop::lang.components.category.slug',
                'description'       => 'bedard.shop::lang.components.category.slug_description',
                'default'           => '{{ :slug }}',
                'type'              => 'dropdown',
            ],
            'default' => [
                'title'             => 'bedard.shop::lang.components.category.default',
                'description'       => 'bedard.shop::lang.components.category.default_description',
                'type'              => 'dropdown',
                'showExternalParam' => false,
            ],
            'page' => [
                'title'             => 'bedard.shop::lang.components.category.page',
                'description'       => 'bedard.shop::lang.components.category.page_description',
                'default'           => '{{ :page }}',
                'type'              => 'string',
                'validationPattern' => '^[1-9][0-9]*$',
                'validationMessage' => Lang::get('bedard.shop::lang.components.category.page_invalid'),
            ],
            'notfound' => [
                'title'             => 'bedard.shop::lang.components.category.notfound',
                'description'       => 'bedard.shop::lang.components.category.notfound_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
            'descriptions' => [
                'group'             => 'bedard.shop::lang.components.category.data',
                'title'             => 'bedard.shop::lang.components.category.load_description',
                'description'       => 'bedard.shop::lang.components.category.load_description_info',
                'type'              => 'checkbox',
                'default'           => false,
                'showExternalParam' => false,
            ],
            'snippets' => [
                'group'             => 'bedard.shop::lang.components.category.data',
                'title'             => 'bedard.shop::lang.components.category.load_snippet',
                'description'       => 'bedard.shop::lang.components.category.load_snippet_info',
                'type'              => 'checkbox',
                'default'           => false,
                'showExternalParam' => false,
            ],
            'thumbnails' => [
                'group'             => 'bedard.shop::lang.components.category.data',
                'title'             => 'bedard.shop::lang.components.category.thumbnails',
                'description'       => 'bedard.shop::lang.components.category.thumbnails_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
            'gallery' => [
                'group'             => 'bedard.shop::lang.components.category.data',
                'title'             => 'bedard.shop::lang.components.category.gallery',
                'description'       => 'bedard.shop::lang.components.category.gallery_description',
                'type'              => 'checkbox',
                'default'           => false,
                'showExternalParam' => false,
            ],
            'inventories' => [
                'group'             => 'bedard.shop::lang.components.category.data',
                'title'             => 'bedard.shop::lang.components.category.inventories',
                'description'       => 'bedard.shop::lang.components.category.inventories_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Loads the available categories
     *
     * @return  array
     */
    public function getCategorySlugs()
    {
        return CategoryModel::orderBy('name', 'asc')->lists('name', 'slug');
    }

    public function getDefaultOptions()
    {
        return $this->getCategorySlugs();
    }

    public function getSlugOptions()
    {
        return $this->getCategorySlugs();
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        // Query the selected category
        $slug = $this->property('slug') ?: $this->property('default');
        if (!$this->category = CategoryModel::where('slug', $slug)->first()) {
            return $this->property('notfound')
                ? $this->controller->run('404')
                : false;
        }

        // Load the pagination
        $this->loadPagination();

        // Determine if descriptions should be selected
        $select = [];
        if ($this->property('snippets')) $select[] = 'snippet_html';
        if ($this->property('descriptions')) $select[] = 'description_html';

        // Determine which relationships to load
        $relationships = [];
        if ($this->property('gallery')) $relationships[] = 'images';
        if ($this->property('thumbnails')) $relationships[] = 'thumbnails';
        if ($this->property('inventories')) $relationships[] = 'inventories';

        // Execute the product query
        $this->products = $this->category->getProducts($this->pagination['current'], $select, $relationships);
        if ($this->rows == 0) {
            $this->totalProducts = $this->products->count();
        }
    }

    /**
     * Loads a category's pagination data
     */
    protected function loadPagination()
    {
        // Non-paginated categories
        if ($this->category->rows == 0) {
            $this->pagination = [
                'first'     => 1,
                'last'      => 1,
                'current'   => 1,
                'next'      => false,
                'previous'  => false,
            ];
        }

        // Paginated categories
        else {
            $this->totalProducts = $this->category->countProducts();

            $perPage    = $this->category->rows * $this->category->columns;
            $last       = ceil($this->totalProducts / $perPage);
            $current    = intval($this->property('page'));
            if ($current > $last) $current = $last;
            if ($current < 1) $current = 1;

            $this->pagination = [
                'first'     => 1,
                'last'      => ceil($this->totalProducts / $perPage),
                'current'   => $current,
                'next'      => $current < $last ? $current + 1 : false,
                'previous'  => $current > 1 ? $current - 1 : false,
            ];
        }
    }
}
