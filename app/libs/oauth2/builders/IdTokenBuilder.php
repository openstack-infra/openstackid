<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace oauth2\builders;

use jwt\IBasicJWT;
use jwt\impl\JWTClaimSet;
use oauth2\models\IClient;
use oauth2\models\JWTResponseInfo;

/**
 * Interface IdTokenBuilder
 * @package oauth2\builders
 */
interface IdTokenBuilder
{
    /**
     * @param JWTClaimSet $claim_set
     * @param JWTResponseInfo $info
     * @param IClient $client
     * @return IBasicJWT
     */
    public function buildJWT(JWTClaimSet $claim_set, JWTResponseInfo $info, IClient $client);
}