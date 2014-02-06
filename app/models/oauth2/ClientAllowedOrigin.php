<?php

use utils\model\BaseModelEloquent;

class ClientAllowedOrigin extends  BaseModelEloquent{

    protected $table = 'oauth2_client_allowed_origin';

    public function client(){
        return $this->belongsTo('Client');
    }
} 