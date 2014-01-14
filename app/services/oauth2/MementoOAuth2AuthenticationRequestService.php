<?php

namespace services\oauth2;

use Input;
use oauth2\OAuth2Message;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\OAuth2Request;
use Session;

class MementoOAuth2AuthenticationRequestService implements IMementoOAuth2AuthenticationRequestService
{

    /**
     * Save current OAuth2AuthorizationRequest till next request
     * @return bool
     */
    public function saveCurrentAuthorizationRequest()
    {
        $input         = Input::all();
        $oauth2_params = array();
        foreach ($input as $key => $value) {
            if (array_key_exists($key, OAuth2AuthorizationRequest::$params) === true) {
                array_push($oauth2_params, $key);
            }
        }

        if (count($oauth2_params) > 0) {
            Input::flashOnly($oauth2_params);
            return true;
        } else {
            $old_data = Input::old();
            $oauth2_params = array();
            foreach ($old_data as $key => $value) {
                if (array_key_exists($key, OAuth2AuthorizationRequest::$params) === true) {
                    array_push($oauth2_params, $key);
                }
            }
            if (count($oauth2_params) > 0) {
                Session::reflash();
                return true;
            }
        }

        return false;

    }

    /** Retrieve last OAuth2AuthorizationRequest
     * @return OAuth2AuthorizationRequest;
     */
    public function getCurrentAuthorizationRequest()
    {
        $msg = new OAuth2AuthorizationRequest(new OAuth2Message(Input::all()));
        if (!$msg->isValid()) {
            //if not valid , then check on old input
            $msg = null;
            $old_data = Input::old();
            $oauth2_params = array();
            foreach ($old_data as $key => $value) {
                if (array_key_exists($key, OAuth2AuthorizationRequest::$params) === true) {
                    $oauth2_params[$key] = $value;
                }
            }
            if (count($oauth2_params) > 0) {
                $msg = new OAuth2AuthorizationRequest(new OAuth2Message($oauth2_params));
            }
        }
        return $msg;
    }

    public function clearCurrentRequest()
    {
        $old_data      = Input::old();
        $oauth2_params = array();

        foreach ($old_data as $key => $value) {
            if (array_key_exists($key, OAuth2AuthorizationRequest::$params) === true){
                array_push($oauth2_params, $key);
            }
        }

        if (count($oauth2_params) > 0) {
            foreach ($oauth2_params as $oauth2_param) {
                Session::forget($oauth2_param);
                Session::remove($oauth2_param);
            }
        }
    }


}