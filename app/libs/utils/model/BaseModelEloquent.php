<?php

namespace utils\model;
use Eloquent;
use ReflectionClass;
/**
 * Class BaseModelEloquent
 * @package utils\model
 */
abstract class BaseModelEloquent extends Eloquent {

    private $class = null;
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

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->class  = new ReflectionClass(get_class($this));
        if ($this->useSti()) {
            $this->setAttribute($this->stiClassField, $this->class->getName());
        }
    }

    private function useSti() {
        return ($this->stiClassField && $this->stiBaseClass);
    }

    public function newQuery($excludeDeleted = true)
    {
        $builder = parent::newQuery($excludeDeleted);
        // If I am using STI, and I am not the base class,
        // then filter on the class name.
        if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this)) {
            $builder->where($this->stiClassField, "=", $this->class->getShortName());
        }
        return $builder;
    }

    public function newFromBuilder($attributes = array())
    {
        if ($this->useSti() && $attributes->{$this->stiClassField}) {
            $class = $this->class->getName();
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes((array) $attributes, true);
            return $instance;
        } else {
            return parent::newFromBuilder($attributes);
        }
    }
} 