<?php

namespace repositories;

use auth\IUserRepository;
use auth\User;
use DB;
use utils\services\ILogService;
use \Member;

/**
 * Class EloquentUserRepository
 * @package repositories
 */
final class EloquentUserRepository extends AbstractEloquentEntityRepository implements IUserRepository
{


    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * EloquentUserRepository constructor.
     * @param User $user
     * @param ILogService $log_service
     */
    public function __construct(User $user, ILogService $log_service)
    {
        $this->entity      = $user;
        $this->log_service = $log_service;
    }

    /**
     * @param $id
     * @return User
     */
    public function get($id)
    {
        return $this->entity->find($id);
    }

    public function getByCriteria($filters)
    {
        return $this->entity->Filter($filters)->get();
    }

    public function getOneByCriteria($filters)
    {
        return $this->entity->Filter($filters)->first();
    }

     /**
     * @param array $filters
     * @return int
     */
    public function getCount(array $filters = array())
    {
        return $this->entity->Filter($filters)->count();
    }

    /**
     * @param $external_id
     * @return User
     */
    public function getByExternalId($external_id)
    {
        return $this->entity->where('external_identifier', '=', $external_id)->first();
    }

    /**
     * @param mixed $identifier
     * @param string $token
     * @return User
     */
    public function getByToken($identifier, $token)
    {
        return $this->entity
            ->where('external_identifier', '=', $identifier)
            ->where('remember_token', '=',$token)->first();
    }

    /**
     * @param string $term
     * @return array
     */
    public function getByEmailOrName($term)
    {
        $list    = array();
        $members = Member::where('Email', 'like', '%'.$term.'%')->paginate(10);
        foreach($members->getItems() as $m)
        {
            $user = $this->getByExternalId(intval($m->ID));
            if(!is_null($user))
                array_push($list, $user);
        }
        return $list;
    }

    /**
     * @param string $user_identifier
     * @return User
     */
    public function getByIdentifier($user_identifier)
    {
        return $this->entity->where('identifier', '=', $user_identifier)->first();
    }
}