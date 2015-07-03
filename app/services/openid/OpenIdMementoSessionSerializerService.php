<?php

namespace services\openid;

use openid\requests\OpenIdMessageMemento;
use openid\services\IMementoOpenIdSerializerService;

use Session;

/**
 * Class OpenIdMementoSessionSerializerService
 * @package services\openid
 */
class OpenIdMementoSessionSerializerService implements IMementoOpenIdSerializerService
{

    /**
     * @param OpenIdMessageMemento $memento
     * @return void
     */
    public function serialize(OpenIdMessageMemento $memento)
    {
        $state = base64_encode(json_encode($memento->getState()));
        Session::put('openid.request.state', $state);
        Session::save();
    }

    /**
     * @return OpenIdMessageMemento
     */
    public function load()
    {
        $state = Session::get('openid.request.state', null);

        if(is_null($state)) return null;

        $state = json_decode( base64_decode($state), true);

        return OpenIdMessageMemento::buildFromState($state);
    }

    /**
     * @return void
     */
    public function forget()
    {
        Session::remove('openid.request.state');
        Session::save();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return Session::has('openid.request.state');
    }
}