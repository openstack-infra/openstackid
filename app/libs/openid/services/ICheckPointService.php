<?php

namespace openid\services;
use \Exception;

/**
 * Interface ICheckPointService
 * Defines the contract to implement Checkpoint Pattern
 * depicted on Architectural Patterns for Enabling Application Security - Yoder/Barcalow
 * @package openid\services
 */
interface ICheckPointService {

    /**Check available securities policies
     * @return boolean
     */
    public function check();

    /**
     * Keeps track of exceptions
     * @param Exception $ex
     * @return mixed
     */
    public function trackException(Exception $ex);

    public function addPolicy(ISecurityPolicy $policy);
} 