<?php namespace Bedard\Shop\Widgets;

use Backend\Classes\WidgetBase;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
use DB;
use Flash;
use Lang;

class ReorderCategories extends WidgetBase
{
    protected $defaultAlias = 'reorderCategories';

    /**
     * Returns information about this widget.
     *
     * @return  array
     */
    public function widgetDetails()
    {
        return [
            'name'          => 'Reorder Categories',
            'description'   => 'Reorder shop categories and manage category nesting.'
        ];
    }

    /**
     * Inject CSS and JS assets
     */
    public function injectAssets()
    {
        $this->addCss('css/widget.css');
        $this->addJs('js/jquery-sortable.js');
        $this->addJs('js/widget.js');
    }

    /**
     * Load the re-order popup
     */
    public function onLoadPopup()
    {
        return $this->makePartial('widget', [
            'categories' => (new Category)->setTreeOrderBy('position', 'asc')->getAllRoot(),
        ]);
    }

    /**
     * Update each category with it's new parent_id and position,
     * then trigger syncProducts() on everything.
     */
    public function onUpdateCategories()
    {
        if (!$categories = input('bedard_shop_categories')) {
            return;
        }

        foreach ($categories as $data) {
            DB::table('bedard_shop_categories')
                ->where('id', $data['id'])
                ->update([
                    'parent_id' => $data['parent_id'] ?: null,
                    'position'  => $data['position'] ?: null,
                ]);
        }

        Product::syncProducts(Product::all());

        Flash::success(Lang::get('bedard.shop::lang.categories.reorder_success'));
        return $this->controller->listRefresh();
    }
}
