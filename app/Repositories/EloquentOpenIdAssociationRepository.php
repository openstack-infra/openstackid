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
use OpenId\Repositories\IOpenIdAssociationRepository;
use Models\OpenId\OpenIdAssociation;
/**
 * Class EloquentOpenIdAssociationRepository
 * @package repositories
 */
class EloquentOpenIdAssociationRepository extends AbstractEloquentEntityRepository implements IOpenIdAssociationRepository
{
    /**
     * EloquentOpenIdAssociationRepository constructor.
     * @param OpenIdAssociation $association
     */
    public function __construct(OpenIdAssociation $association)
    {
        $this->entity = $association;
    }

    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }

    public function getByHandle($handle)
    {
        return $this->entity->where('identifier', '=', $handle)->first();
    }
}