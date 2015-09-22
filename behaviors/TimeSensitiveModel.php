<?php namespace Bedard\Shop\Behaviors;

use DB;
use System\Classes\ModelBehavior;

/**
 * Time Sensitive Model
 *
 * Adds a variety of validation, scopes, and accessors useful for
 * determining if a model is running or not based on a start_at
 * and end_at properties.
 */
class TimeSensitiveModel extends ModelBehavior {

    /**
     * @var Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->addDateAttribute('start_at');
        $model->addDateAttribute('end_at');
        $model->addFillable(['start_at', 'end_at']);

        if (in_array('October\Rain\Database\Traits\Validation', class_uses($model))) {
            $model->rules['start_at'] = 'date';
            $model->rules['end_at'] = 'date|after:start_at';

            // todo: figure out how to get $customMessages in here
        }

        $this->model = $model;
    }

    /**
     * Query Scopes
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function scopeIsExpired($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where(function($query) use ($now) {
            $query->whereNotNull('end_at')
                  ->where('end_at', '<=', $now);
        });
    }

    public function scopeIsRunning($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query
            ->where(function($query) use ($now) {
                $query->whereNull('start_at')
                      ->orWhere('start_at', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('end_at')
                      ->orWhere('end_at', '>', $now);
            });
    }

    public function scopeIsUpcoming($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where(function($query) use ($now) {
            $query->whereNotNull('start_at')
                  ->where('start_at', '>', $now);
        });
    }

    public function scopeIsRunningOrUpcoming($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where(function($query) use ($now) {
            $query->whereNull('end_at')
                  ->orWhere('end_at', '>', $now);
        });
    }

    public function scopeSelectStatus($query)
    {
        $now = date('Y-m-d H:i:s');
        $table = $this->model->table;
        $query->addSelect(DB::raw("(".
            "CASE ".
                "WHEN (`$table`.`end_at` IS NOT NULL AND `$table`.`end_at` < '$now') THEN 2 ".
                "WHEN (`$table`.`start_at` IS NOT NULL AND `$table`.`start_at` >= '$now') THEN 1 ".
                "ELSE 0 ".
            "END".
        ") as `status`"));
    }

    /**
     * Accessors and Mutators
     *
     * @return  boolean
     */
    public function getIsExpiredAttribute()
    {
        return $this->model->end_at && strtotime($this->model->end_at) <= time();
    }

    public function getIsRunningAttribute()
    {
        return (!$this->model->start_at || strtotime($this->model->start_at) <= time()) &&
               (!$this->model->end_at || strtotime($this->model->end_at) > time());
    }

    public function getIsUpcomingAttribute()
    {
        return $this->model->start_at && strtotime($this->model->start_at) > time();
    }
}
