<?php
namespace oauth2\services;

interface IApiScopeService {
    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names);

    /** get all active scopes
     * @return mixed
     */
    public function getAvailableScopes();

    public function getAudienceByScopeNames(array $scopes_names);
} 