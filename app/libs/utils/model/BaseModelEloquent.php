<?php

namespace utils\model;
use Eloquent;

/**
 * Class BaseModelEloquent
 * @package utils\model
 */
abstract class BaseModelEloquent extends Eloquent {

    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters){
        foreach($filters as $filter){
            $query = $query->where($filter['name'],$filter['op'], $filter['value']);
        }
        return $query;
    }
} 