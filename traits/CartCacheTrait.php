<?php namespace Bedard\Shop\Traits;

trait CartCacheTrait {

    /**
     * Helper scope to select only cacheable columns
     */
    public function scopeSelectCacheable($query)
    {
        return $query->addSelect($this->cacheable);
    }
}
