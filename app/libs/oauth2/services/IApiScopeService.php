<?php
namespace oauth2\services;

interface IApiScopeService {
    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names);

    public function getFriendlyScopesByName(array $scopes_names);

    /**
     * @param bool $system
     * @return array|mixed
     */
    public function getAvailableScopes($system=false);

    public function getAudienceByScopeNames(array $scopes_names);

    public function getStrAudienceByScopeNames(array $scopes_names);
} 