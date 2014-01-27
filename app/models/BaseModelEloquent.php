<?php


abstract class BaseModelEloquent extends Eloquent {

    public function scopeFilter($query, array $filters){
        foreach($filters as $filter){
            $query = $query->where($filter['name'],$filter['op'], $filter['value']);
        }
        return $query;
    }
} 