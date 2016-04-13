<?php namespace Utils\Model;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Illuminate\Database\Eloquent\Model;
use Utils\JsonUtils;
use ReflectionClass;
/**
 * Class BaseModelEloquent
 * @package Utils\Model
 */
class BaseModelEloquent extends Model implements IEntity
{

    private $class = null;

    protected $array_mappings = array();

    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $filter) {
            $query = $query->where($filter['name'], $filter['op'], $filter['value']);
        }
        return $query;
    }

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->class = new ReflectionClass(get_class($this));
        if ($this->useSti()) {
            $this->setAttribute($this->stiClassField, $this->class->getShortName());
        }
    }

    public function toArray()
    {
        $values = parent::toArray();

        if (count($this->array_mappings)) {
            $new_values = array();
            foreach ($this->array_mappings as $old_key => $new_key) {
                $value = isset($values[$old_key])? $values[$old_key] :
                    (
                    isset($values['pivot'])? (
                    isset($values['pivot'][$old_key]) ? $values['pivot'][$old_key] : null
                    ): null
                    );

                $new_key = preg_split('/:/',$new_key);
                if(count($new_key) > 1)
                {
                    //we have a formatter ...
                    switch(strtolower($new_key[1]))
                    {
                        case 'datetime_epoch':
                        {
                            if(!is_null($value)) {
                                $datetime = new \DateTime($value);
                                $value = $datetime->getTimestamp();
                            }
                        }
                            break;
                        case 'json_string':
                        {
                            $value = JsonUtils::toJsonString($value);
                        }
                            break;
                        case 'json_boolean':
                        {
                            $value = JsonUtils::toJsonBoolean($value);
                        }
                            break;
                        case 'json_int':
                        {
                            $value = JsonUtils::toJsonInt($value);
                        }
                            break;
                        case 'json_float':
                        {
                            $value = JsonUtils::toJsonFloat($value);
                        }
                            break;
                    }
                }
                $new_values[$new_key[0]] = $value;
            }
            $values = $new_values;
        }

        return $values;
    }

    private function useSti()
    {
        return ($this->stiClassField && $this->stiBaseClass);
    }

    private function useMti()
    {
        return $this->mtiClassType;
    }

    public function newQuery($excludeDeleted = true)
    {
        $builder = parent::newQuery($excludeDeleted);
        // If I am using STI, and I am not the base class,
        // then filter on the class name.
        if ($this->useMti()) {
            $query              = $builder->getQuery();
            $class              = $this->class->getName();
            $parents            = $this->get_class_lineage(new $class);
            $base_table_set     = false;
            $current_class_name = null;

            if ($this->mtiClassType === 'concrete') {
                $current_class_name = $this->class->getShortName();
                $query = $query->from($current_class_name);
                $base_table_set = true;
            }

            foreach ($parents as $parent) {

                if(!$this->isAllowedParent($parent))
                {
                    continue;
                }

                $parent = new $parent;
                if ($parent->mtiClassType === 'abstract') {
                    continue;
                }

                $table_name = $parent->class->getShortName();

                if ($base_table_set === true) {
                    $query->leftJoin($table_name, $current_class_name . '.ID', '=', $table_name . '.ID');
                } else {
                    $query = $query->from($table_name);
                    $base_table_set = true;
                    $current_class_name = $table_name;
                }
            }

        } else {
            if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this)) {
                $builder->where($this->stiClassField, "=", $this->class->getShortName());
            }
        }

        return $builder;
    }

    protected function isAllowedParent($parent_name)
    {
        $res = str_contains($parent_name, $this->class->getName()) ||
            str_contains($parent_name, Model::class) ||
            str_contains($parent_name, BaseModelEloquent::class);
        return !$res;
    }

    private function get_class_lineage($object)
    {
        $class_name = get_class($object);
        $parents = array_values(class_parents($class_name));

        return array_merge(array($class_name), $parents);
    }

    public function newFromBuilder($attributes = array(), $connection = null)
    {
        if ($this->useSti() && $attributes->{$this->stiClassField}) {
            $class = $this->class->getName();
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes((array)$attributes, true);

            return $instance;
        } else {
            return parent::newFromBuilder($attributes, $connection);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }
}