<?php
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomValidator
 * Custom validation methods
 */
class CustomValidator extends Validator {

    protected $implicitRules = array('Required', 'RequiredWith', 'RequiredWithout', 'RequiredIf', 'Accepted', 'RequiredWithoutField');

    public function __construct(TranslatorInterface $translator, $data, $rules, $messages = array())
    {
        parent::__construct($translator, $data, $rules, $messages);
        $this->isImplicit('fail');
    }

    public function validateBoolean($attribute, $value, $parameters)
    {
        if(is_bool($value))
            return true;
        if(is_int($value))
            return true;
        return strtoupper(trim($value))==='TRUE' || strtoupper(trim($value))==='FALSE' || strtoupper(trim($value))==='1' || strtoupper(trim($value))==='0' ;
    }

    public function validateText($attribute, $value, $parameters)
    {
        $value = trim($value);
        return preg_match("/^[a-zA-Z0-9\s\-\.\,]+$/", $value) == 1;
    }

    public function validateHttpmethod($attribute, $value, $parameters){
        $value = strtoupper(trim($value));
        //'GET', 'HEAD','POST','PUT','DELETE','TRACE','CONNECT','OPTIONS'
        $allowed_http_verbs = array(
            'GET'=>'GET',
            'HEAD'=>'HEAD',
            'POST'=>'POST',
            'PUT'=>'PUT',
            'DELETE'=>'DELETE',
            'TRACE'=>'TRACE',
            'CONNECT'=>'CONNECT',
            'OPTIONS'=>'OPTIONS',
        );

        return array_key_exists($value,$allowed_http_verbs);
    }

    public function validateRoute($attribute, $value, $parameters){
        return true;
    }

    public function validateScopename($attribute, $value, $parameters){
        $value = trim($value);
        return preg_match("/^[a-zA-Z0-9\-\.\,\:\_\/]+$/", $value) == 1;
    }

    public function validateHost($attribute, $value, $parameters){
        return true;
    }

    public function validateApplicationtype($attribute, $value, $parameters){
        return true;
    }
} 