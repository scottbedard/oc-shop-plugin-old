<?php namespace Bedard\Shop\Traits;

trait DateActiveTrait {

    /**
     * Return only models that are active
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function scopeIsActive($query)
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

    /**
     * Returns all models that are not expired
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function scopeIsActiveOrUpcoming($query)
    {
        $now = date('Y-m-d H:i:s');
        return $query->where(function($query) use ($now) {
            $query->whereNull('end_at')
                  ->orWhere('end_at', '>', $now);
        });
    }

}
