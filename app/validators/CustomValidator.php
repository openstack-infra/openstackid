<?php
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;

class CustomValidator extends Validator {

    protected $implicitRules = array('Required', 'RequiredWith', 'RequiredWithout', 'RequiredIf', 'Accepted', 'RequiredWithoutField');

    public function __construct(TranslatorInterface $translator, $data, $rules, $messages = array())
    {
        parent::__construct($translator, $data, $rules, $messages);
        $this->isImplicit('fail');
    }

    public function validateBoolean($attribute, $value, $parameters)
    {
        return strtoupper(trim($value))==='TRUE' || strtoupper(trim($value))==='FALSE' || strtoupper(trim($value))==='1' || strtoupper(trim($value))==='0' ;
    }

    public function validateText($attribute, $value, $parameters)
    {
        $value = trim($value);
        return preg_match("/^[a-zA-Z0-9]+$/", $value) == 1;
    }
} 