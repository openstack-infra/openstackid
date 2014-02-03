<?php

use utils\model\BaseModelEloquent;

class ServerConfiguration extends BaseModelEloquent
{
    public $timestamps = false;
    protected $table = 'server_configuration';
} 