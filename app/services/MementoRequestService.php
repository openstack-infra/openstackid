<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:29 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\OpenIdMessage;
use openid\services\IMementoOpenIdRequestService;
use \Input;
use \Session;

class MementoRequestService implements IMementoOpenIdRequestService {

    /**
     * Save current openid message to temp storage for next request
     * @return bool
     */
    public function saveCurrentRequest()
    {
        $input = Input::all();
        $openid_params = array();
        foreach($input as $key=>$value){
            if(stristr($key,"openid")!==false){
                array_push($openid_params,$key);
            }
        }
        if(count($openid_params)>0){
            Input::flashOnly($openid_params);
            return true;
        }
        else{
            $old_data = Input::old();
            $openid_params = array();
            foreach($old_data as $key=>$value){
                if(stristr($key,"openid")!==false){
                    array_push($openid_params,$key);
                }
            }
            if(count($openid_params)>0){
                Session::reflash();
                return true;
            }
        }

        return false;
    }

    public function getCurrentRequest()
    {
        $msg = new OpenIdMessage(Input::all());
        if (!$msg->IsValid()) {
            $msg = null;
            $old_data = Input::old();
            $openid_params = array();
            foreach($old_data as $key=>$value){
                if(stristr($key,"openid")!==false){
                    $openid_params[$key]=$value;
                }
            }
            if(count($openid_params)>0){
                $msg = new OpenIdMessage($openid_params);
            }
        }
        return $msg;
    }

    public function clearCurrentRequest(){
        $old_data = Input::old();
        $openid_params = array();
        foreach($old_data as $key=>$value){
            if(stristr($key,"openid")!==false){
                array_push($openid_params,$key);
            }
        }
        if(count($openid_params)>0){
           foreach($openid_params as $open_id_param){
               Session::forget($open_id_param);
               Session::remove($open_id_param);
           }
        }
    }
}