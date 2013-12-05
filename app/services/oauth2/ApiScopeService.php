<?php

namespace services\oauth2;

use oauth2\services\IApiScopeService;
use ApiScope;

class ApiScopeService implements IApiScopeService{

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names)
    {
        return ApiScope::where('active','=',true)->whereIn('name',$scopes_names)->get();
    }
}