<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:33 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\requests\contexts;
use \string;

class PartialView {

    private $name;
    private $data;

    public function __construct($name,array $data = null){
        $this->name = $name;
        $this->data = $data;
    }

    public function getData(){
        return $this->data;
    }

    public function getName(){
        return $this->name;
    }
}