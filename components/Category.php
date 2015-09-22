<?php namespace Bedard\Shop\Components;

use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Bedard\Shop\Models\Category as CategoryModel;
use Bedard\Shop\Models\Product as ProductModel;

class Category extends ComponentBase
{

    /**
     * The category being viewed
     *
     * @var CategoryModel
     */
    public $category;

    /**
     * The category's products
     *
     * @var Collection
     */
    public $products;

    /**
     * Message to display when there are no messages.
     *
     * @var string
     */
    public $noProductsMessage;

    /**
     * Reference to the page name for linking to products.
     *
     * @var string
     */
    public $productPage;

    public $columns;
    public $rows;

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
                'showExternalParam' => false,
            ],
            'default' => [
                'title'             => 'bedard.shop::lang.components.category.default',
                'description'       => 'bedard.shop::lang.components.category.default_description',
                'type'              => 'dropdown',
                'showExternalParam' => false,
            ],
            'productPage' => [
                'title'       => 'Product page',
                'description' => 'Name of the product page file.',
                'type'        => 'dropdown',
                'showExternalParam' => false,
            ],
            'rows'   => [
                'title'             => 'Rows',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the rows value',
                'default'           => '3',
                'showExternalParam' => false,
            ],
            'columns' => [
                'title'             => 'Columns',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the rows value',
                'default'           => '4',
                'showExternalParam' => false,
            ],
            'noProductsMessage' => [
                'title'       => 'No products message',
                'description' => 'Message to display in the product list in case if there are no products.',
                'type'        => 'string',
                'default'     => 'No products found',
                'showExternalParam' => false,
            ],
            'sortOrder' => [
                'title'       => 'Product order',
                'description' => 'Attribute on which the products should be ordered',
                'type'        => 'dropdown',
                'default'     => 'created_at desc',
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Loads the available categories
     *
     * @return  array
     */
    public function getDefaultOptions() {
        return ['' => '- None -'] + CategoryModel::orderBy('name', 'asc')->lists('name', 'slug');
    }

    public function getProductPageOptions() {
        return Page::withComponent('shopProduct')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        return ProductModel::$allowedSortingOptions;
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->getCategory();
        return $this->prepareProductList();
    }

    public function onFilter()
    {
        $this->prepareVars();
        $this->prepareProductList();
    }


    protected function prepareVars()
    {
        $this->rows = $this->property('rows');
        $this->columns = $this->property('columns');
        $this->noProductsMessage = $this->page['noProductsMessage'] = $this->property('noProductsMessage');

        /*
         * Page links
         */
        $this->productPage = $this->page['productPage'] = $this->property('productPage');
    }

    public function getCategory()
    {
        $slug = $this->property('slug') ?: $this->property('default');
        return CategoryModel::whereSlug($slug)->first();
    }

    protected function prepareProductList()
    {
        /*
         * If category exists, load the products
         */
        if ($category = $this->getCategory()) {
            $currentPage = input('page');
            $searchString = trim(input('search'));
            $sortOrder = input('sort', $this->property('sortOrder'));
            $perPage = input('per_page', $this->rows * $this->columns);
            $products = ProductModel::with('categories')->listFrontEnd([
                'page'     => $currentPage,
                'sort'     => $sortOrder,
                'perPage'  => $perPage,
                'search'   => $searchString,
                'category' => $category->id
            ]);

            /*
             * Add a "url" helper attribute for linking to each product
             */
            $products->each(function($product) {
                $product->setUrl($this->productPage, $this->controller);
            });

            $this->page['products'] = $this->products = $products;

            /*
             * Pagination
             */
            if ($products) {
                $queryArr = [];
                if ($searchString) {
                    $queryArr['search'] = $searchString;
                }

                if ($perPage) {
                    $queryArr['per_page'] = $perPage;
                }

                if ($sortOrder) {
                    $queryArr['sort'] = $sortOrder;
                }

                $queryArr['page'] = '';
                $paginationUrl = Request::url() . '?' . http_build_query($queryArr);
                if ($currentPage > ($lastPage = $products->lastPage()) && $currentPage > 1) {
                    return Redirect::to($paginationUrl . $lastPage);
                }

                $this->page['paginationUrl'] = $paginationUrl;
            }
        }
    }
}
