<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;
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
     * Ajax Handlers
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

    public function onDeleteInventory()
    {
        return $this->deleteModel(Inventory::find(input('id')), 'bedard.shop::lang.inventories.model');
    }

    public function onDeleteOption()
    {
        return $this->deleteModel(Option::find(input('id')), 'bedard.shop::lang.options.model');
    }

    public function onProcessInventory()
    {
        $inventory = Inventory::findOrNew(input('model_id'));
        return $this->saveModel($inventory, ['sku', 'quantity', 'modifier'], 'inventories');
    }

    public function onProcessOption()
    {
        $option = Option::findOrNew(input('model_id'));
        return $this->saveModel($option, ['name', 'placeholder'], 'options');
    }

    /**
     * Displys a popup form
     */
    protected function displayForm($model, $dir, $lang, $handler)
    {
        $form = $this->makeConfig("$/bedard/shop/models/$dir/fields.yaml");
        $form->model = $model;
        $form->model->product_id = $this->model->id;

        $header = $form->model->id
            ? Lang::get('backend::lang.relation.update_name', ['name' => Lang::get($lang)])
            : Lang::get('backend::lang.relation.create_name', ['name' => Lang::get($lang)]);

        return $this->makePartial('form', [
            'header'    => $header,
            'handler'   => $handler,
            'model'     => $form->model,
            'form'      => $this->makeWidget('Backend\Widgets\Form', $form),
        ]);
    }

    /**
     * Deletes an Option or Inventory
     *
     * @param   Option|Inventory    $model      The model being deleted
     * @param   string              $name       The lang string of the model type
     * @return  mixed
     */
    protected function deleteModel($model, $name)
    {
        if ($model) {
            $model->delete();
            Flash::success(Lang::get('backend::lang.form.delete_success', ['name' => Lang::get($name)]));
        }

        return $this->renderPartials();
    }

    /**
     * Creates or updates an Option or Inventory
     *
     * @param   Option|Inventory    $model      The model being created or updated
     * @param   array               $fields     The fields being saved
     * @param   string              $type       The name of the relationship and lang resource
     * @return  mixed
     */
    protected function saveModel($model, $fields, $type)
    {
        $model->product_id = $this->model->id ?: null;
        foreach ($fields as $field) {
            $model->$field = input($field);
        }

        $context = $model->id ? 'update' : 'create';
        $model->saveWithValues(input('valueIds'), input('valueNames'));
        if (!$model->product_id) {
            $this->model->$type()->add($model, input('sessionKey'));
        }

        Flash::success(Lang::get("backend::lang.form.{$context}_success", ['name' => Lang::get("bedard.shop::lang.$type.model")]));
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
