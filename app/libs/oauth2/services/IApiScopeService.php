<?php

namespace oauth2\services;

use oauth2\models\IApiScope;

/**
 * Interface IApiScopeService
 * @package oauth2\services
 */
interface IApiScopeService {
    /**
     * gets an api scope by id
     * @param $id id of api scope
     * @return IApiScope
     */
    public function get($id);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10, array $filters=array(), array $fields=array('*'));

    /**
     * @param IApiScope $scope
     * @return bool
     */
    public function save(IApiScope $scope);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiScope
     */
    public function update($id, array $params);

    /**
     * sets api scope status (active/deactivated)
     * @param $id id of api scope
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id,$status);

    /**
     * deletes an api scope
     * @param $id id of api scope
     * @return bool
     */
    public function delete($id);

    /**
     * Creates a new api scope instance
     * @param $name
     * @param $short_description
     * @param $description
     * @param $active
     * @param $default
     * @param $system
     * @param $api_id
     * @return IApiScope
     */
    public function add($name, $short_description, $description, $active, $default, $system, $api_id);

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names);

    /**
     * Given an array of scopes names, gets friendly names for given ones
     * @param array $scopes_names
     * @return mixed
     */
    public function getFriendlyScopesByName(array $scopes_names);

    /**
     * Get all active scopes (system/non system ones)
     * @param bool $system
     * @return array|mixed
     */
    public function getAvailableScopes($system=false);

    /**
     * Given a set of scopes names, retrieves a list of resource server owners
     * @param array $scopes_names
     * @return mixed
     */
    public function getAudienceByScopeNames(array $scopes_names);

    /**
     * gets audience string for a given scopes sets (resource servers)
     * @param array $scopes_names
     * @return mixed
     */
    public function getStrAudienceByScopeNames(array $scopes_names);

    /**
     * gets a list of default scopes
     * @return mixed
     */
    public function getDefaultScopes();
} 