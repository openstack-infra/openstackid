<?php

namespace repositories;

use auth\IMemberRepository;
use Member;
use utils\services\ILogService;

/**
 * Class EloquentMemberRepository
 * @package repositories
 */
class EloquentMemberRepository implements IMemberRepository
{

	private $member;
	private $log_service;

    /**
     * @param Member $member
     * @param ILogService $log_service
     */
    public function __construct(Member $member, ILogService $log_service)
    {
        $this->member = $member;
        $this->log_service = $log_service;
    }
	/**
	 * @param $id
	 * @return Member
	 */
	public function get($id)
	{
		return $this->member->find($id);
	}

	/**
	 * @param $email
	 * @return Member
	 */
	public function getByEmail($email)
	{
		return $this->member->where('Email', '=', $email)->first();
	}
}