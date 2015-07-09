<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Option;
use Lang;
use Flash;

/**
 * OptionsInventories Form Widget
 */
class OptionsInventories extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bedard_shop_options_inventories';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('optionsinventories');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        // These language strings will be used by our javascript asset
        $this->vars['lang'] = json_encode([
            'relation.delete_confirm'   => Lang::get('backend::lang.relation.delete_confirm'),
            'form.cancel'               => Lang::get('backend::lang.form.cancel'),
            'form.confirm'              => Lang::get('backend::lang.form.confirm'),
            'Option.delete_text'        => Lang::get('bedard.shop::lang.options.delete_text'),
            'value.delete_text'         => Lang::get('bedard.shop::lang.values.delete_text'),
        ]);

        $sessionKey = input('sessionKey') ?: $this->sessionKey;

        $this->model->load([
            'inventories' => function($inventory) use ($sessionKey) {
                $inventory->withDeferred($sessionKey)->with('values');
            },
            'options' => function($option) use ($sessionKey) {
                $option->withDeferred($sessionKey)->with('values')->orderBy('position');
            },
        ]);

        $this->vars['inventories']  = $this->model->inventories;
        $this->vars['options']      = $this->model->options;
    }

    /**
     * Prepare widget variables and push updates to the Options
     * and Inventoryies partials.
     *
     * @return  array
     */
    public function renderPartials()
    {
        $this->prepareVars();

        return [
            '#formInventories'  => $this->makePartial('inventories'),
            '#formOptions'      => $this->makePartial('options'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/optionsinventories.css', 'Bedard.Shop');
        $this->addJs('js/html.sortable.min.js', 'Bedard.Shop');
        $this->addJs('js/optionsinventories.js', 'Bedard.Shop');
    }

    /**
     * Display the Inventory or Options form
     */
    public function onDisplayInventory()
    {
        $inventory = Inventory::findOrNew(input('id'));
        return $this->displayForm($inventory, 'inventory', 'bedard.shop::lang.inventories.model', 'onProcessInventory');
    }

    public function onDisplayOption()
    {
        $option = Option::findOrNew(input('id'));
        return $this->displayForm($option, 'option', 'bedard.shop::lang.options.model', 'onProcessOption');
    }

    protected function displayForm($model, $dir, $lang, $handler)
    {
        $form = $this->makeConfig("$/bedard/shop/models/$dir/fields.yaml");
        $form->model = $model;
        $form->model->product_id = $this->model->id;

        return $this->makeForm($form, Lang::get($lang), $handler);
    }

    /**
     * Makes a popup form
     *
     * @return  mixed
     */
    protected function makeForm($form, $modelName, $handler)
    {
        $header = $form->model->id
            ? Lang::get('backend::lang.relation.update_name', ['name' => $modelName])
            : Lang::get('backend::lang.relation.create_name', ['name' => $modelName]);

        return $this->makePartial('form', [
            'header'    => $header,
            'handler'   => $handler,
            'model'     => $form->model,
            'form'      => $this->makeWidget('Backend\Widgets\Form', $form),
        ]);
    }

    /**
     * Create or update an inventory
     *
     * @return  array
     */
    public function onProcessInventory()
    {
        $inventoryId            = input('model_id');
        $inventory              = Inventory::findOrNew($inventoryId);
        $inventory->product_id  = $this->model->id ?: null;
        $inventory->sku         = input('sku');
        $inventory->quantity    = input('quantity');
        $inventory->modifier    = input('modifier');
        $inventory->saveWithValues(input('valueIds'));

        if (!$inventory->product_id) {
            $this->model->inventories()->add($inventory, input('sessionKey'));
        }

        $this->contextFlash($inventoryId, 'bedard.shop::lang.inventories.model');
        return $this->renderPartials();
    }

    /**
     * Create or update an option
     *
     * @return  array
     */
    public function onProcessOption()
    {
        $optionId               = intval(input('model_id'));
        $option                 = Option::findOrNew($optionId);
        $option->product_id     = $this->model->id ?: null;
        $option->name           = input('name');
        $option->placeholder    = input('placeholder');
        $option->saveWithValues(input('valueIds'), input('valueNames'));

        if (!$option->product_id) {
            $this->model->options()->add($option, input('sessionKey'));
        }

        $this->contextFlash($optionId, 'bedard.shop::lang.options.model');
        return $this->renderPartials();
    }

    protected function contextFlash($update, $name)
    {
        $context = $update ? 'update' : 'create';
        Flash::success(Lang::get("backend::lang.form.{$context}_success", ['name' => Lang::get($name)]));
    }

    /**
     * Delete an Option or Inventory
     */
    public function onDeleteInventory()
    {
        return $this->deleteAndFlash(Inventory::find(input('id')), 'bedard.shop::lang.inventories.model');
    }

    public function onDeleteOption()
    {
        return $this->deleteAndFlash(Option::find(input('id')), 'bedard.shop::lang.options.model');
    }

    protected function deleteAndFlash($model, $name)
    {
        if ($model) {
            $success = $model->delete();
            Flash::success(Lang::get('backend::lang.form.delete_success', ['name' => Lang::get($name)]));
        }

        return $this->renderPartials();
    }

    /**
     * Update option positions
     */
    public function getSaveValue($value)
    {
        if (!$options = input('options')) {
            return;
        }

        foreach ($options as $position => $id) {
            $option = Option::find($id);
            $option->position = $position;
            $option->save();
        }
    }
}
