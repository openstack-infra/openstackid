<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/4/13
 * Time: 4:06 PM
 */

class Api  extends Eloquent {
    protected $table = 'oauth2_api';

    public function scopes()
    {
        return $this->hasMany('ApiScope','api_id');
    }
} 