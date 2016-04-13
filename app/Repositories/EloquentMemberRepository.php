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
use Auth\Repositories\IMemberRepository;
use Models\Member;
use Utils\Services\ILogService;
/**
 * Class EloquentMemberRepository
 * @package Repositories
 */
final class EloquentMemberRepository extends AbstractEloquentEntityRepository implements IMemberRepository
{

    private $member;
    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * @param Member $member
     * @param ILogService $log_service
     */
    public function __construct(Member $member, ILogService $log_service)
    {
        $this->entity      = $member;
        $this->log_service = $log_service;
    }

    /**
     * @param $email
     * @return Member
     */
    public function getByEmail($email)
    {
        return $this->entity->where('Email', '=', $email)->first();
    }
}