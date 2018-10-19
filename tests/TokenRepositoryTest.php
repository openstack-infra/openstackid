<?php
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
use Tests\TestCase;
/**
 * Class TokenRepositoryTest
 */
final class TokenRepositoryTest extends TestCase
{
    public function testAccessTokenRepository(){
        $repository = $this->app[\OAuth2\Repositories\IAccessTokenRepository::class];
        $this->assertTrue(!is_null($repository));
        $this->assertTrue($repository instanceof \OAuth2\Repositories\IAccessTokenRepository);
        $paginator = $repository->getAllValidByClientIdentifier(105);
        $this->assertTrue($paginator->total() == 0);
    }
}