<?php

namespace oauth2\models;


interface IApi {
    /**
     * @return IResourceServer
     */
    public function getResourceServer();

    public function getName();

    public function getLogo();

    public function getRoute();

    public function getHttpMethod();

    public function getDescription();

    public function getScope();

    public function isActive();
} 