<?php
namespace oauth2;

use utils\http\HttpMessage;

class OAuth2Message extends HttpMessage
{

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

}