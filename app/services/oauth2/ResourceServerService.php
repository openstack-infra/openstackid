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
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ResourceServer::Filter($filters)->paginate($page_size,$fields);
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidResourceServer
     */
    public function update($id, array $params){

        $res = false;
        DB::transaction(function () use ($id,$params,&$res) {
            $resource_server = ResourceServer::find($id);
            if(is_null($resource_server))
                throw new InvalidResourceServer(sprintf('resource server id %s does not exists!',$id));
            $allowed_update_params = array('host','ip','active','friendly_name');
            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){

                    if($param =='host'){
                        if(ResourceServer::where('host','=',$params[$param])->where('id','<>',$id)->count()>0)
                            throw new InvalidResourceServer(sprintf('there is already another resource server with that hostname (%s).',$params[$param]));
                    }
                    if($param =='friendly_name'){
                        if(ResourceServer::where('friendly_name','=',$params[$param])->where('id','<>',$id)->count()>0)
                            throw new InvalidResourceServer(sprintf('there is already another resource server with that friendly name (%s).',$params[$param]));
                    }
                    $resource_server->{$param} = $params[$param];
                }
            }
            $res = $this->save($resource_server);
        });
        return $res;
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
        return true;
    }

    /**
     * sets resource server status (active/deactivated)
     * @param $id id of resource server
     * @param bool $status status (active/non active)
     * @return bool
     */
    public function setStatus($id, $status)
    {
        return ResourceServer::find($id)->update(array('active'=>$status));
    }

    /**
     * deletes a resource server
     * @param id $id
     * @return bool
     */
    public function delete($id)
    {
        $res = false;
        DB::transaction(function () use ($id,&$res) {
            $resource_server = ResourceServer::find($id);
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
     * @param $id id of resource server
     * @return IResourceServer
     */
    public function get($id)
    {
        return ResourceServer::find($id);
    }

    /** Creates a new resource server instance
     * @param $host
     * @param $ip
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function add($host, $ip, $friendly_name, $active)
    {
        $instance = null;
        if(is_string($active)){
            $active = $active==='true'?true:false;
        }
        DB::transaction(function () use ($host, $ip, $friendly_name, $active, &$instance) {

            if(ResourceServer::where('host','=',$host)->count()>0)
                throw new InvalidResourceServer(sprintf('there is already another resource server with that hostname (%s).',$host));

            if(ResourceServer::where('friendly_name','=',$friendly_name)->count()>0)
                throw new InvalidResourceServer(sprintf('there is already another resource server with that friendly name (%s).',$friendly_name));

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
            $new_client = $this->client_service->addClient(IClient::ApplicationType_Service,null,$host.'.confidential.application',$friendly_name.' confidential oauth2 application');
            $new_client->resource_server()->associate($instance);
            $new_client->Save();
        });
        return $instance;
    }

    /**
     * @param $id
     * @return bool
     */
    public function regenerateClientSecret($id){
        $res = null;
        DB::transaction(function () use ($id,&$res) {
            $resource_server = ResourceServer::find($id);
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
