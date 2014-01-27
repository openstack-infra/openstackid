<?php

use utils\services\ILogService;

abstract class AbstractRESTController extends JsonController {

    protected $allowed_filter_fields;
    protected $allowed_filter_op;
    protected $allowed_filter_value;

    private $filter_delimiter;
    private $field_delimiter;

    public function __construct(ILogService $log_service){
        parent::__construct($log_service);
        $this->filter_delimiter = '|';
        $this->field_delimiter  = ':';
    }
    /**
     * @param $filters
     * @return array
     */

    protected function getFilters($filters){

        if(!is_string($filters)) return array();
        if(empty($filters)) return array();

        $filter_args = explode($this->filter_delimiter,$filters);
        $res = array();
        foreach($filter_args as $exp){

            $exp = explode($this->field_delimiter,$exp);

            if(!is_array($exp) || count($exp)!=3) continue;
            if(!in_array($exp[0],$this->allowed_filter_fields)) continue;
            if(!in_array($exp[1],$this->allowed_filter_op[$exp[0]])) continue;
            if(preg_match($this->allowed_filter_value[$exp[0]],$exp[2])!=1) continue;

            array_push($res,array(
                'name'  => $exp[0],
                'op'    => $exp[1],
                'value' => $exp[2],
            ));
        }
        return $res;
    }
} 