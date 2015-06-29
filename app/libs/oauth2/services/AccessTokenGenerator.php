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

namespace oauth2\services;

use oauth2\OAuth2Protocol;
use utils\model\Identifier;
use utils\services\UniqueIdentifierGenerator;
use Zend\Math\Rand;

/**
 * Class AccessTokenGenerator
 * @package oauth2\services
 */
final class AccessTokenGenerator extends UniqueIdentifierGenerator {

    /**
     * @param Identifier $identifier
     * @return Identifier
     */
    protected function _generate(Identifier $identifier)
    {
        $identifier->setValue(Rand::getString($identifier->getLenght(), OAuth2Protocol::VsChar, true));
        return $identifier;
    }
}