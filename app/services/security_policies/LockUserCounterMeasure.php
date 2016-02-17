<?php

namespace services;

use auth\IUserRepository;
use Exception;
use Log;
use openid\services\IUserService;
use utils\services\ISecurityPolicyCounterMeasure;
use utils\services\IServerConfigurationService;

class LockUserCounterMeasure implements ISecurityPolicyCounterMeasure
{
    private $server_configuration;
    private $user_service;
    private $repository;

    public function __construct(
        IUserRepository $repository,
        IUserService $user_service,
        IServerConfigurationService $server_configuration
    ) {
        $this->user_service = $user_service;
        $this->server_configuration = $server_configuration;
        $this->repository = $repository;
    }

    public function trigger(array $params = array())
    {
        try {

            if (!isset($params["user_identifier"])) {
                return;
            }
            $user_identifier = $params["user_identifier"];

            $user = $this->repository->getByExternalId($user_identifier);
            if (is_null($user)) {
                return;
            }
            //apply lock policy
            if (intval($user->login_failed_attempt) < intval($this->server_configuration->getConfigValue("MaxFailed.Login.Attempts"))) {
                $this->user_service->updateFailedLoginAttempts($user->id);
            } else {
                $this->user_service->lockUser($user->id);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}