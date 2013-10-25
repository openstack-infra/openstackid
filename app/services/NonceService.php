<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/24/13
 * Time: 9:11 PM
 */

namespace services;
//use Illuminate\Redis\Database as Redis;
use openid\services\INonceService;
use openid\exceptions\ReplayAttackException;

class NonceService implements INonceService {

    private $redis;

    public function __construct(){
        $this->redis = \RedisLV4::connection();
    }

    public function generateNonce()
    {
        $nonce = gmdate('Y-m-d\TH:i:s\Z') . uniqid();
        //sets the $nonce to live 60 secs
        $this->redis->setex($nonce,3600 ,'');
        return $nonce;
    }

    /**
     * @param $nonce
     * @param $signature
     * @throws \openid\exceptions\ReplayAttackException
     */
    public function markNonceAsInvalid($nonce, $signature)
    {
        $old_signature =   $this->redis->get($nonce);
        if(!$old_signature){
            throw new ReplayAttackException(sprintf("nonce %s was already used!.",$nonce));
        }
        if($old_signature!=$signature){
            throw new ReplayAttackException(sprintf("nonce %s was associated with sig %s, but sig %s was provided.",$nonce,$old_signature,$signature));
        }
        $this->redis->del($nonce);
    }

    public function associateNonce($nonce, $signature)
    {
          $this->redis->setex($nonce,3600,$signature);
    }
}