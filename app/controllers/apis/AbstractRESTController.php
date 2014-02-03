<?php

use utils\services\ILogService;

abstract class AbstractRESTController extends JsonController {

    protected $allowed_filter_fields;
    protected $allowed_projection_fields;

    private $filter_delimiter;
    private $field_delimiter;



    public function __construct(ILogService $log_service){
        parent::__construct($log_service);
        $this->filter_delimiter = '+';
        $this->field_delimiter  = ',';
    }

    protected function getProjection($fields){
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

    protected function getFilters($filters){
        if(!is_array($filters)) return array();
        $res = array();
        foreach($filters as $fieldname=>$value){
            if(in_array($fieldname,$this->allowed_filter_fields)){
                array_push($res,array('name'=>$fieldname,'op'=>'=','value'=>$value));
            }
        }
        return $res;
    }
} 