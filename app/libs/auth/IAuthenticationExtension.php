<?php

namespace auth;


interface IAuthenticationExtension {

    public function process(User $user);
} 