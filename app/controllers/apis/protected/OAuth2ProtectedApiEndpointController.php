<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2ProtectedApiEndpointController
 * OAuth2 Protected API
 */
class OAuth2ProtectedApiEndpointController  extends OAuth2ProtectedController
{
    private $controller;

    public function __construct(ApiEndpointController $controller, IResourceServerContext $resource_server_context,  ILogService $log_service)
    {
        parent::__construct($resource_server_context,$log_service);
        $this->controller = $controller;
    }

    public function get($id)
    {
        return $this->controller->get($id);
    }

    public function getByPage($page_nbr, $page_size)
    {
        return $this->controller->getByPage($page_nbr, $page_size);
    }

    public function create()
    {
        return $this->controller->create();
    }

    public function delete($id)
    {
        return $this->controller->delete($id);
    }

    public function update()
    {
        return $this->controller->update();
    }

    public function updateStatus($id, $active){
        return $this->controller->updateStatus($id, $active);
    }

    public function addRequiredScope($id, $scope_id){
        return $this->controller->addRequiredScope($id, $scope_id);
    }

    public function removeRequiredScope($id, $scope_id){
        return $this->controller->removeRequiredScope($id, $scope_id);
    }
} 