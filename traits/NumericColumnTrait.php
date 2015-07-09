<?php namespace Bedard\Shop\Traits;

use DB;

trait NumericColumnTrait {

    /**
     * This scope is used to help make columns sortable when they contain
     * an monetary value or a percentage value.
     *
     * @param   string  $table      The model table
     * @param   string  $left       The left side of the condition
     * @param   string  $then       The value to use if the condition is true
     * @param   string  $else       The value to use if the condition is false
     * @param   string  $as         The column alias
     * @param   sting   $right      The right side of the condition
     * @return  DB
     */
    public function selectNumeric($table, $left, $then, $else, $as = 'amount', $right = '1')
    {
        return DB::raw("(CASE WHEN `$table`.`$left` = $right THEN `$table`.`$then` ELSE `$table`.`$else` END) as `$as`");
    }
}
