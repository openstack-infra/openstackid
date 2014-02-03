<?php
use  oauth2\models\IUserConsent;
use utils\model\BaseModelEloquent;
/**
 * Class UserConsent
 */
class UserConsent extends BaseModelEloquent implements IUserConsent {

    protected $table = 'oauth2_user_consents';

    public function user()
    {
        return $this->belongsTo('auth\User');
    }

    public function client()
    {
        return $this->belongsTo('Client');
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getClient()
    {
        return $this->client()->first();
    }

    public function getUser()
    {
        return $this->user()->first();
    }
}