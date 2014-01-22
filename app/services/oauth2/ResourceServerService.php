<?php


namespace services\oauth2;

use oauth2\models\IResourceServer;
use oauth2\models\IClient;
use oauth2\services\id;
use oauth2\services\IResourceServerService;
use oauth2\services\IClientService;
use ResourceServer;
use DB;
use \oauth2\exceptions\InvalidResourceServer;

class ResourceServerService implements IResourceServerService {

    private $client_service;

    public function __construct(IClientService $client_service){
        $this->client_service = $client_service;
    }

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size = 10, $page_nbr = 1)
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ResourceServer::paginate($page_size);
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidResourceServer
     */
    public function update($id, array $params){

        $resource_server = ResourceServer::find($id);
        if(is_null($resource_server))
            throw new InvalidResourceServer(sprintf('resource server id %s does not exists!',$id));

        $allowed_update_params = array('host','ip','active','friendly_name');
        foreach($allowed_update_params as $param){
            if(array_key_exists($param,$params)){
                $resource_server->{$param} = $params[$param];
            }
        }
        return $this->save($resource_server);
    }

    /**
     * @param IResourceServer $resource_server
     * @return bool
     */
    public function save(IResourceServer $resource_server)
    {
        if(!$resource_server->exists() || count($resource_server->getDirty())>0){
            return $resource_server->Save();
        }
        return false;
    }

    /**
     * sets resource server status (active/deactivated)
     * @param $resource_server_id id of resource server
     * @param bool $status status (active/non active)
     * @return bool
     */
    public function setStatus($resource_server_id, $status)
    {
        return ResourceServer::find($resource_server_id)->update(array('active'=>$status));
    }

    /**
     * deletes a resource server
     * @param $resource_server_id id of resource server
     * @return bool
     */
    public function delete($resource_server_id)
    {
        $res = false;
        DB::transaction(function () use ($resource_server_id,&$res) {
            $resource_server = ResourceServer::find($resource_server_id);
            if(!is_null($resource_server)){
                $client = $resource_server->client()->first();
                if(!is_null($client)){
                    $this->client_service->deleteClientByIdentifier($client->id);
                }
                $resource_server->delete();
                $res = true;
            }
        });
        return $res;
    }

    /**
     * get a resource server by id
     * @param $resource_server_id id of resource server
     * @return IResourceServer
     */
    public function get($resource_server_id)
    {
        return ResourceServer::find($resource_server_id);
    }

    /** Creates a new resource server instance
     * @param $host
     * @param $ip
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function addResourceServer($host, $ip, $friendly_name, $active)
    {
        $instance = null;
        if(is_string($active)){
            $active = $active==='true'?true:false;
        }
        DB::transaction(function () use ($host, $ip, $friendly_name, $active, &$instance) {
            $instance = new ResourceServer(
                array(
                    'host'          => $host,
                    'ip'            => $ip,
                    'active'        => $active,
                    'friendly_name' => $friendly_name
                )
            );

            $instance->Save();

            // creates a new client for this brand new resource server
            $new_client = $this->client_service->addClient(IClient::ClientType_Confidential,null,$host.'.confidential.application',$friendly_name.' confidential oauth2 application');
            $new_client->resource_server()->associate($instance);
            $new_client->Save();
        });
        return $instance;
    }

    /**
     * @param $resource_server_id
     * @return bool
     */
    public function regenerateResourceServerClientSecret($resource_server_id){
        $res = null;
        DB::transaction(function () use ($resource_server_id,&$res) {
            $resource_server = ResourceServer::find($resource_server_id);
            if(!is_null($resource_server)){
                $client = $resource_server->client()->first();
                if(!is_null($client)){
                    $res = $this->client_service->regenerateClientSecret($client->id);
                }
            }
        });
        return $res;
    }
}
