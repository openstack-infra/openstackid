<?php


use utils\model\BaseModelEloquent;

class ClientAuthorizedUri extends BaseModelEloquent {

    protected $table = 'oauth2_client_authorized_uri';

    public function client(){
        return $this->belongsTo('Client');
    }
} 