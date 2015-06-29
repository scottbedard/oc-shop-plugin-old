<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * RelationSelector Form Widget
 */
class RelationSelector extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bedard_shop_relation_selector';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $relationship = $this->formField->fieldName;
        $models = $this->model->$relationship->sortBy($this->config->key);

        $this->vars['models'] = $models;
        $this->vars['input'] = $models->lists('id');

        $this->prepareVars();
        return $this->makePartial('relationselector');
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/relationselector.css', 'Bedard.Shop');
        $this->addJs('js/relationselector.js', 'Bedard.Shop');
    }

    /**
     * Prepare widget variables
     */
    public function prepareVars()
    {
        $this->vars['alias'] = $this->alias;
        $this->vars['fieldName'] = $this->formField->arrayName.'['.$this->formField->fieldName.'][]';
        $this->vars['key'] = $this->config->key;

        $this->vars['partial'] = isset($this->config->partial)
            ? $this->config->partial
            : 'default';

        $this->vars['addText'] = isset($this->config->addText)
            ? $this->config->addText
            : 'backend::lang.relation.add';

        $this->vars['removeText'] = isset($this->config->removeText)
            ? $this->config->removeText
            : 'backend::lang.relation.remove';

        $this->vars['addIcon'] = isset($this->config->addIcon)
            ? 'oc-'.$this->config->addIcon
            : '';

        $this->vars['removeIcon'] = isset($this->config->removeIcon)
            ? 'oc-'.$this->config->removeIcon
            : '';

        $this->vars['popupHeader'] = isset($this->config->popupHeader)
            ? $this->config->popupHeader
            : 'backend::lang.relation.add_selected';

        $this->vars['popupEmpty'] = isset($this->config->popupEmpty)
            ? $this->config->popupEmpty
            : 'backend::lang.list.no_records';

        $this->vars['popupPlaceholder'] = isset($this->config->popupPlaceholder)
            ? $this->config->popupPlaceholder
            : 'backend::lang.list.search_prompt';

        $this->vars['selectionEmpty'] = isset($this->config->selectionEmpty)
            ? $this->config->selectionEmpty
            : 'backend::lang.list.no_records';
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    /**
     * Executes a query to search related models
     */
    protected function searchRelation()
    {
        $relation   = new $this->config->class;
        $key   = $this->config->key;
        $search     = input('search');

        $scope = isset($this->config->scope)
            ? $this->config->scope
            : false;

        // Pagination variables
        if ($scope) {
            $total = $search
                ? $relation::$scope()->where($key, 'LIKE', "%$search%")->count()
                : $relation::$scope()->count();
        } else {
            $total = $search
                ? $relation::where($key, 'LIKE', "%$search%")->count()
                : $relation::count();
        }

        $perPage    = 10;
        $lastPage   = ceil($total / $perPage);
        $page       = input('page') ?: 1;
        $skip       = ($page - 1) * $perPage;

        // Build the query
        $order = input('order') ?: 'asc';
        $query = $relation::limit($perPage)->skip($skip);
        $query->orderBy($key, $order);
        if ($search) {
            $query->where($key, 'LIKE', "%$search%");
        }

        if (isset($this->config->scope)) {
            $scope = $this->config->scope;
            $query->$scope();
        }

        // Pass variables to the partials
        $this->vars['search']       = $search ?: '';
        $this->vars['order']        = $order;
        $this->vars['page']         = $page;
        $this->vars['pagePrevious'] = $page - 1 >= 1 ? $page - 1 : false;
        $this->vars['pageNext']     = $page + 1 <= $lastPage ? $page + 1 : false;
        $this->vars['results']      = $query->get();
        $this->vars['resultsFrom']  = ($page - 1) * $perPage + 1;
        $this->vars['resultsTo']    = $this->vars['resultsFrom'] + $this->vars['results']->count() - 1;
        $this->vars['resultsTotal'] = $total;
    }

    /**
     * Searches a relation and update the results container
     */
    public function onSearch()
    {
        $this->searchRelation();
        $this->prepareVars();

        return [
            '.relationselector-modal .results' => $this->makePartial('search'),
        ];
    }

    /**
     * Returns a popup form to select related models
     */
    public function onSelectRelations()
    {
        $this->searchRelation();
        $this->prepareVars();
        return $this->makePartial('popup');
    }

    /**
     * Updates the attached table
     */
    public function onUpdateAttached()
    {
        $attached = input('attached') ?: [];

        $relation = new $this->config->class;
        $key = $this->config->key;

        $this->vars['models'] = $relation::whereIn('id', $attached)
            ->orderBy($key, 'asc')
            ->get();

        $this->vars['input'] = $relation::select('id')->whereIn('id', $attached)->lists('id');
        $this->prepareVars();

        return [
            '#'.$this->alias.' .input' => $this->makePartial('input'),
            '#'.$this->alias.' .attached' => $this->makePartial('attached'),
        ];
    }
}
