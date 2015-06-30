<?php namespace Bedard\Shop\Behaviors;

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
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->dates = ['start_at', 'end_at'];
        $model->addFillable(['start_at', 'end_at']);

        if (in_array('October\Rain\Database\Traits\Validation', class_uses($model))) {
            $model->rules['start_at'] = 'date';
            $model->rules['end_at'] = 'date|after:start_at';

            // todo: figure out how to get $customMessages in here
        }
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
