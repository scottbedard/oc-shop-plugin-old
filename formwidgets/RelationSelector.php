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
        $this->vars['alias']            = $this->alias;
        $this->vars['fieldName']        = $this->formField->arrayName.'['.$this->formField->fieldName.'][]';
        $this->vars['key']              = $this->config->key;
        $this->vars['partial']          = $this->getConfig('partial', 'default');
        $this->vars['addText']          = $this->getConfig('addText', 'backend::lang.relation.add');
        $this->vars['removeText']       = $this->getConfig('removeText', 'backend::lang.relation.remove');
        $this->vars['addIcon']          = $this->getConfig('addIcon');
        $this->vars['removeIcon']       = $this->getConfig('removeIcon');
        $this->vars['popupHeader']      = $this->getConfig('popupHeader', 'backend::lang.relation.add_selected');
        $this->vars['popupEmpty']       = $this->getConfig('popupEmpty', 'backend::lang.list.no_records');
        $this->vars['popupPlaceholder'] = $this->getConfig('popupPlaceholder', 'backend::lang.list.search_prompt');
        $this->vars['selectionEmpty']   = $this->getConfig('selectionEmpty');
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    /**
     * Puts together the search results for the related models
     */
    protected function searchRelation()
    {
        $perPage    = 10;
        $search     = input('search');
        $page       = input('page') ?: 1;
        $key        = $this->config->key;
        $relation   = new $this->config->class;

        $total = $this->countSearch($relation, $search, $key);
        $this->executeSearch($relation, $search, $key, $page, $perPage);
        $this->preparePagination($page, $perPage, $total);
    }

    /**
     * Counts the number of results returned by the search
     *
     * @param   Model       $relation   The model being searched
     * @param   string      $search     The search term
     * @param   string      $key        The column being searched
     * @return  integer
     */
    protected function countSearch($relation, $search, $key)
    {
        $scope = isset($this->config->scope)
            ? $this->config->scope
            : false;

        if ($scope) {
            $total = $search
                ? $relation::$scope()->where($key, 'LIKE', "%$search%")->count()
                : $relation::$scope()->count();
        } else {
            $total = $search
                ? $relation::where($key, 'LIKE', "%$search%")->count()
                : $relation::count();
        }

        $this->vars['resultsTotal'] = $total;
        return $total;
    }

    /**
     * Execute the search and pass the results to the view
     *
     * @param   Model       $relation   The model being searched
     * @param   string      $search     The search term
     * @param   string      $key        The column being searched
     * @param   integer     $page       The page number
     * @param   integer     $perPage    Reults per page
     */
    protected function executeSearch($relation, $search, $key, $page, $perPage)
    {
        $skip   = ($page - 1) * $perPage;
        $order  = input('order') ?: 'asc';
        $query  = $relation::limit($perPage)->skip($skip)->orderBy($key, $order);

        if ($search) {
            $query->where($key, 'LIKE', "%$search%");
        }

        if (isset($this->config->scope) && ($scope = $this->config->scope)) {
            $query->$scope();
        }

        // Pass necessary variables to the view
        $this->vars['order']    = $order;
        $this->vars['search']   = $search ?: '';
        $this->vars['results']  = $query->get();
    }

    /**
     * Prepares pagination variables
     *
     * @param   integer     $page       The current page
     * @param   integer     $perPage    The number of items per page
     * @param   integer     $total      The total number of results
     */
    protected function preparePagination($page, $perPage, $total)
    {
        $this->vars['page'] = $page;

        $this->vars['pagePrevious'] = $page > 1
            ? $page - 1
            : false;

        $this->vars['pageNext'] = $page + 1 <= ceil($total / $perPage)
            ? $page + 1
            : false;

        $this->vars['resultsFrom']  = ($page - 1) * $perPage + 1;
        $this->vars['resultsTo']    = $this->vars['resultsFrom'] + $this->vars['results']->count() - 1;
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
