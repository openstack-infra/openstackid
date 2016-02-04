<?php

namespace auth;

use utils\db\IBaseRepository;

/**
 * Interface IUserRepository
 * @package auth
 */
interface IUserRepository extends IBaseRepository
{

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


    /**
     * @param string $term
     * @return array
     */
    public function getByEmailOrName($term);

    /**
     * @param string $user_identifier
     * @return User
     */
    public function getByIdentifier($user_identifier);
} 