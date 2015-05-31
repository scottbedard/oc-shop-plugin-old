<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Option;
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
        $this->vars['options'] = Option::where('product_id', $this->model->id)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/optionsinventories.css', 'Bedard.Shop');
        $this->addJs('js/optionsinventories.js', 'Bedard.Shop');
    }

    /**
     * Display a form to create or update a product option
     *
     * @return  makePartial()
     */
    public function onDisplayOption()
    {
        $form = $this->makeConfig('$/bedard/shop/models/option/fields.yaml');

        if ($id = input('id')) {
            $form->model = Option::findOrNew($id);
            $header = 'update';
        } else {
            $form->model = new Option;
            $header = 'create';
        }

        return $this->makePartial('form', [
            'header'        => $header,
            'handler'       => 'onProcessOption',
            'model'         => $form->model,
            'product_id'    => $this->model->id,
            'form'          => $this->makeWidget('Backend\Widgets\Form', $form),
        ]);
    }

    /**
     * Create or update a product option
     *
     * @return  array
     */
    public function onProcessOption()
    {
        $option = Option::findOrNew(intval(input('id')));
        $option->name = input('name');
        $option->product_id = input('product_id');
        $option->placeholder = input('placeholder');
        $option->save();

        Flash::success('hooray, it worked');
        $this->prepareVars();

        return [
            '#formOptions' => $this->makePartial('options')
        ];
    }
}
