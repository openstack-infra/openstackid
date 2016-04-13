<?php namespace App\Http\Controllers\Api;

/**
 * Copyright 2015 OpenStack Foundation
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
use Utils\Services\ILogService;
/**
 * Class AbstractRESTController
 * @package App\Http\Controllers\Apis
 */
abstract class AbstractRESTController extends JsonController
{


    protected $allowed_filter_fields;
    protected $allowed_projection_fields;

    protected $filter_delimiter;
    protected $field_delimiter;

    /**
     * AbstractRESTController constructor.
     * @param ILogService $log_service
     */
    public function __construct(ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->filter_delimiter = '+';
        $this->field_delimiter  = ',';
    }

    protected function getProjection($fields)
    {
        if(!is_string($fields)) return array('*');
        if(empty($fields)) return array('*');
        $fields_args = explode($this->field_delimiter,$fields);
        $res = array();
        foreach($fields_args as $exp){
            if(in_array($exp,$this->allowed_projection_fields)){
                array_push($res,$exp);
            }
        }
        if(!count($res))
            $res = array('*');
        return $res;
    }

    protected function getFilters($filters)
    {
        if(!is_array($filters)) return array();
        $res = array();
        foreach($filters as $fieldname=>$value){
            if(in_array($fieldname,$this->allowed_filter_fields)){
                array_push($res,['name' => $fieldname, 'op' => '=','value' => $value]);
            }
        }
        return $res;
    }
} 