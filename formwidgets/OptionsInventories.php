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

    public function renderPartials()
    {
        $this->prepareVars();

        return [
            '#formOptions' => $this->makePartial('options'),
        ];
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

        // These variables will be used normally by partials
        $this->model->load('options.values', 'inventories.values');
        $this->vars['inventories']  = $this->model->inventories;
        $this->vars['options']      = $this->model->options;
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
     * Display the Inventory popup form
     *
     * @return  makePartial()
     */
    public function onDisplayInventory()
    {
        $form = $this->makeConfig('$/bedard/shop/models/inventory/fields.yaml');
        $form->model = Inventory::findOrNew(intval(input('id')));
        $form->model->product_id = $this->model->id;

        return $this->makeForm(
            $form,
            Lang::get('bedard.shop::lang.inventories.model'),
            'onProcessInventory'
        );
    }

    /**
     * Display the Option popup form
     *
     * @return  makePartial()
     */
    public function onDisplayOption()
    {
        $form = $this->makeConfig('$/bedard/shop/models/option/fields.yaml');
        $form->model = Option::findOrNew(intval(input('id')));
        $form->model->product_id = $this->model->id;

        return $this->makeForm(
            $form,
            Lang::get('bedard.shop::lang.options.model'),
            'onProcessOption'
        );
    }

    /**
     * Makes a popup form
     *
     * @return  makePartial()
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
     * Create or update a product option
     *
     * @return  array
     */
    public function onProcessOption()
    {
        $optionId = intval(input('model_id'));
        $option = Option::findOrNew($optionId);
        $option->name = input('name');
        $option->product_id = intval(input('product_id'));
        $option->placeholder = input('placeholder');
        $option->saveWithValues(input('valueIds'), input('valueNames'));

        $model = Lang::get('bedard.shop::lang.options.model');
        if ($optionId) {
            Flash::success(Lang::get('backend::lang.form.update_success', ['name' => $model]));
        } else {
            Flash::success(Lang::get('backend::lang.form.create_success', ['name' => $model]));
        }

        return $this->renderPartials();
    }

    /**
     * Deletes an option
     */
    public function onDeleteOption()
    {
        $success = false;
        if ($option = Option::find(input('id'))) {
            $success = $option->delete();
        }

        $model = Lang::get('bedard.shop::lang.options.model');
        if ($success) {
            Flash::success(Lang::get('backend::lang.form.delete_success', ['name' => $model]));
        } else {
            Flash::error(Lang::get('bedard.shop::lang.form.delete_failed_name', ['name' => $model]));
        }

        return $this->renderPartials();
    }

    /**
     * Update option positions
     */
    public function getSaveValue($value)
    {
        if (!$options = input('options')) return;

        foreach ($options as $position => $id) {
            $option = Option::find($id);
            $option->position = $position;
            $option->save();
        }
    }
}
