<?php

namespace services\oauth2;

use oauth2\services\IAllowedOriginService;
use Client;
use ClientAllowedOrigin;

/**
 * Class AllowedOriginService
 * @package services\oauth2
 */
class AllowedOriginService implements  IAllowedOriginService{

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return ClientAllowedOrigin::find($id);
    }

    /**
     * @param $uri
     * @return mixed
     */
    public function getByUri($uri)
    {
        return ClientAllowedOrigin::where('allowed_origin','=',$uri)->first();
    }

    /**
     * @param $uri
     * @param $client_id
     * @return bool|int
     */
    public function create($uri, $client_id)
    {
        $origin = new ClientAllowedOrigin();
        $origin->allowed_origin = $uri;
        $client = Client::find($client_id);
        if(!is_null($client)){
            $client->allowed_origins()->save($origin);
            $origin->Save();
            return $origin->id;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $origin = $this->get($id);
        if(!is_null($origin)){
            return $origin->delete();
        }
        return false;
    }

    /**
     * @param $uri
     * @return bool
     */
    public function deleteByUri($uri)
    {
        $origin = $this->getByUri($uri);
        if(!is_null($origin)){
            return $origin->delete();
        }
        return false;
    }
}