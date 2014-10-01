<?php

namespace auth;

/**
 * Interface IUserRepository
 * @package auth
 */
interface IUserRepository {
	/**
	 * @param $id
	 * @return User
	 */
	public function get($id);


	/**
	 * @param $external_id
	 * @return User
	 */
	public function getByExternalId($external_id);

	/**
	 * @param $filters
	 * @return array
	 */
	public function getByCriteria($filters);

	/**
	 * @param $filters
	 * @return User
	 */
	public function getOneByCriteria($filters);

	/**
	 * @param User $u
	 * @return bool
	 */
	public function update(User $u);

	/**
	 * @param User $u
	 * @return bool
	 */
	public function add(User $u);

	/**
	 * @param int   $page_nbr
	 * @param int   $page_size
	 * @param array $filters
	 * @param array $fields
	 * @return array
	 */
	public function getByPage($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'));


	/**
	 * @param array $filters
	 * @return int
	 */
	public function getCount(array $filters = array());

	/**
	 * @param mixed $identifier
	 * @param string $token
	 * @return User
	 */
	public function getByToken($identifier, $token);

} 