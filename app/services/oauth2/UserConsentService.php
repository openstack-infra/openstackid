<?php

namespace services\oauth2;

use oauth2\exceptions\AbsentClientException;
use oauth2\models\IUserConsent;
use oauth2\services\IUserConsentService;
use UserConsent;
use Client;

class UserConsentService implements IUserConsentService{

    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent
     */
    public function get($user_id, $client_id, $scopes)
    {
        return UserConsent::where('user_id','=',$user_id)
            ->where('client_id','=',$client_id)
            ->where('scopes','=',$scopes)->first();
    }

    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent|void
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function add($user_id, $client_id, $scopes)
    {
        $consent = new UserConsent();

        if(is_null(Client::find($client_id)))
            throw new AbsentClientException;

        $consent->client_id = $client_id;
        $consent->user_id   = $user_id;
        $consent->scopes    = $scopes;
        $consent->Save();
    }
}