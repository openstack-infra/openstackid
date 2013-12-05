<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/5/13
 * Time: 4:32 PM
 */

namespace oauth2\requests;


class OAuth2AccessTokenRequest  extends OAuth2Request {

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    public function isValid()
    {
        // TODO: Implement isValid() method.
    }
}