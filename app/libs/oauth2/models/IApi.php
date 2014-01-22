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

    public function setName($name);

    public function setRoute($route);

    public function setHttpMethod($http_method);

    public function setDescription($description);

    public function setStatus($active);

} 