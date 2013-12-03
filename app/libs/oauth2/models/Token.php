<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 6:14 PM
 */

namespace oauth2\models;


class Token {

    protected $value;
    protected $lifetime;
    protected $issued;
    protected $client_id;

    public function getValue(){
        return $this->value;
    }
} 