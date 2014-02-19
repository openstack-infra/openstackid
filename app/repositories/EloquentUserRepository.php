<?php

namespace repositories;

use auth\IUserRepository;
use auth\User;
use utils\services\ILogService;
use DB;

class EloquentUserRepository implements IUserRepository {

	private $user;
	private $log_service;
	public function __construct(User $user,ILogService $log_service){
		$this->user        = $user;
		$this->log_service = $log_service;
	}
	/**
	 * @param $id
	 * @return User
	 */
	public function get($id)
	{
		return $this->user->find($id);
	}

	public function getByCriteria($filters){
		return $this->user->Filter($filters)->get();
	}

	public function getOneByCriteria($filters){
		return $this->user->Filter($filters)->first();
	}

	/**
	 * @param User $u
	 * @return bool
	 */
	public function update(User $u)
	{
		return $u->Save();
	}

	/**
	 * @param User $u
	 * @return bool
	 */
	public function add(User $u)
	{
		return $u->Save();
	}

	/**
	 * @param int   $page_nbr
	 * @param int   $page_size
	 * @param array $filters
	 * @param array $fields
	 * @return array
	 */
	public function getByPage($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
	{
		DB::getPaginator()->setCurrentPage($page_nbr);
		return $this->user->Filter($filters)->paginate($page_size, $fields);
	}

	/**
	 * @param array $filters
	 * @return int
	 */
	public function getCount(array $filters = array())
	{
		return $this->user->Filter($filters)->count();
	}

	/**
	 * @param $external_id
	 * @return User
	 */
	public function getByExternalId($external_id)
	{
		return $this->user->where('external_id', '=', $external_id)->first();
	}
}