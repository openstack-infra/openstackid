<?php
namespace auth;
use Member;

interface IMemberRepository {
	/**
	 * @param $id
	 * @return Member
	 */
	public function get($id);

	/**
	 * @param $email
	 * @return Member
	 */
	public function getByEmail($email);
} 