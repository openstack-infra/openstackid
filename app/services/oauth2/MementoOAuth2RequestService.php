<?php

namespace services\oauth2;

use oauth2\services\IMementoOAuth2RequestService;
use oauth2\services\OAuth2Request;
use oauth2\requests\OAuth2AuthorizationRequest;

class MementoOAuth2RequestService  implements IMementoOAuth2RequestService{

    /**
     * Save current OAuth2AuthorizationRequest till next request
     * @return bool
     */
    public function saveCurrentRequest()
    {
        $input         = Input::all();
        $oauth2_params = array();
        foreach ($input as $key => $value) {
            if (array_key_exists($key,OAuth2AuthorizationRequest::$params) === true) {
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
                if (array_key_exists($key,OAuth2AuthorizationRequest::$params) === true) {
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
    public function getCurrentRequest()
    {
        $msg = new OAuth2AuthorizationRequest(Input::all());
        if (!$msg->isValid()) {
            $msg = null;
            $old_data = Input::old();
            $oauth2_params = array();
            foreach ($old_data as $key => $value) {
                if (array_key_exists($key,OAuth2AuthorizationRequest::$params) === true) {
                    $oauth2_params[$key] = $value;
                }
            }
            if (count($oauth2_params) > 0) {
                $msg = new OAuth2AuthorizationRequest($oauth2_params);
            }
        }
        return $msg;
    }

    public function clearCurrentRequest()
    {
        // TODO: Implement clearCurrentRequest() method.
    }
}