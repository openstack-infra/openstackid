<?php
namespace openid\handlers\strategies;
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 6:20 PM
 */
use openid\responses\OpenIdAssociationSessionResponse;
use Zend\Crypt\Exception\InvalidArgumentException;
use \Zend\Crypt\Exception\RuntimeException;
use openid\exceptions\InvalidDHParam;
interface ISessionAssociationStrategy {
    /**
     * @throws InvalidDHParam|RuntimeException|InvalidArgumentException
     * @return OpenIdAssociationSessionResponse
     */
    public function handle();
} 