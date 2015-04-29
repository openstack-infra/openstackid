<?php

namespace services\utils;

use utils\services\ICacheService;

/**
 * Class RedisCacheService
 * Cache Service Implementation Based on REDIS
 * http://redis.io
 * @package services
 */
class RedisCacheService implements ICacheService {

    //services
    private $redis = null;

    public function __construct(){

    }

    public function boot(){
        if(is_null($this->redis)){
            $this->redis  = \RedisLV4::connection();
        }
    }
    /**
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        $res = 0;
        if ($this->redis->exists($key)) {
            $res = $this->redis->del($key);
        }
        return $res;
    }

    public function deleteArray(array $keys){
        if(count($keys)>0)
            $this->redis->del($keys);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key){
        $res = $this->redis->exists($key);
        return $res>0;
    }

    /**
     * @param $name
     * @param array $values
     * @return mixed
     */
    public function getHash($name, array $values)
    {
        $res = array();
        if($this->redis->exists($name)){
            $cache_values = $this->redis->hmget($name,$values);
            for($i=0;$i<count($cache_values);$i++)
                $res[$values[$i]] = $cache_values[$i];
        }
        return $res;
    }

    public function storeHash($name,array $values, $ttl=0){
        $res = false;
        //stores in REDIS
        if(!$this->redis->exists($name)){
            $this->redis->hmset($name, $values);
            $res = true;
            //sets expiration time
            if($ttl>0) $this->redis->expire($name, $ttl);
        }
        return $res;
    }

    public function incCounter($counter_name, $ttl = 0)
    {
        if($this->redis->setnx($counter_name,1)) {
            $this->redis->expire($counter_name, $ttl);
            return 1;
        }
        else
           return (int)$this->redis->incr($counter_name);
    }

    public function incCounterIfExists($counter_name){
        $res = false;
        if ($this->redis->exists($counter_name)) {
            $this->redis->incr($counter_name);
            $res = true;
        }
        return $res;
    }

    public function addMemberSet($set_name, $member){
        return $this->redis->sadd($set_name, $member);
    }

    public function deleteMemberSet($set_name,$member){
        return $this->redis->srem($set_name,$member);
    }

    public function getSet($set_name){
        return $this->redis->smembers($set_name);
    }

    public function getSingleValue($key){
        return $this->redis->get($key);
    }

    public function setSingleValue($key,$value,$ttl = 0){
        if($ttl>0)
            return $this->redis->setex($key , $ttl, $value);
        else
            return $this->redis->set($key ,$value);
    }

    public function addSingleValue($key, $value, $ttl = 0){
        $res = $this->redis->setnx($key , $value);
        if($res && $ttl>0)
            $this->redis->expire($key,$ttl);
        return $res;
    }

    public function setKeyExpiration($key, $ttl){
        $this->redis->expire($key, intval($ttl));
    }

    /**Returns the remaining time to live of a key that has a timeout.
     * @param string $key
     * @return int
     */
    public function ttl($key)
    {
        return (int)$this->redis->ttl($key);
    }
}