<?php

namespace openid\strategies;

interface IOpenIdResponseStrategy
{
    public function handle($response);
}