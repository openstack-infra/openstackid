<?php

namespace openid\services;
use openid\model\IOpenIdUser;
/**
 * Interface IUserService
 * @package openid\services
 */
interface IUserService
{

    public function get($id);

	/**
	 * @param IOpenIdUser $user
	 * @param             $proposed_username
	 * @return bool|IOpenIdUser
	 */
	public function associateUser(IOpenIdUser &$user , $proposed_username);

    /**
     * @param $identifier
     * @return mixed
     */
    public function updateLastLoginDate($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function updateFailedLoginAttempts($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function lockUser($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function unlockUser($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function activateUser($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function deActivateUser($identifier);

    /**
     * @param $identifier
     * @param $show_pic
     * @param $show_full_name
     * @param $show_email
     * @return mixed
     */
    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*'));
}