<?php namespace Repositories;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\Repositories\IOpenIdTrustedSiteRepository;
use Models\OpenId\OpenIdTrustedSite;
/**
 * Class EloquentOpenIdTrustedSiteRepository
 * @package Repositories
 */
class EloquentOpenIdTrustedSiteRepository extends AbstractEloquentEntityRepository implements IOpenIdTrustedSiteRepository
{

    /**
     * EloquentOpenIdTrustedSiteRepository constructor.
     * @param OpenIdTrustedSite $openid_trusted_site
     */
    public function __construct(OpenIdTrustedSite $openid_trusted_site)
    {
        $this->entity = $openid_trusted_site;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }

    /**
     * @param int $user_id
     * @param array $sub_domains
     * @param array $data
     * @return array
     */
    public function getMatchingOnesByUserId($user_id, array $sub_domains, array $data)
    {
        $query = $this->entity->where("user_id", "=", intval($user_id));
        //add or condition for all given sub-domains
        if (count($sub_domains)) {
            $query = $query->where(function ($query) use ($sub_domains) {
                foreach ($sub_domains as $sub_domain) {
                    $query = $query->orWhere(function ($query_aux) use ($sub_domain) {
                        $query_aux->where('realm', '=', $sub_domain);
                    });
                }
            });
        }
        //add conditions for all possible pre approved data
        foreach ($data as $value) {
            $query = $query->where("data", "LIKE", '%"' . $value . '"%');
        }
        return $query->get();
    }
}