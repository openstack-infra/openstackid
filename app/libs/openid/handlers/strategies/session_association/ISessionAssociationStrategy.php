<?php

namespace openid\handlers\strategies;

use openid\responses\OpenIdAssociationSessionResponse;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;
use openid\exceptions\InvalidDHParam;

/**
 * Interface ISessionAssociationStrategy
 * @package openid\handlers\strategies
 */
interface ISessionAssociationStrategy {
    /**
     * @throws InvalidDHParam|RuntimeException|InvalidArgumentException
     * @return OpenIdAssociationSessionResponse
     */
    public function handle();
} 