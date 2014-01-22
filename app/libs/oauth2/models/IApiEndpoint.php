<?php

namespace oauth2\models;


interface IApiEndpoint {

    public function getRoute();
    public function getHttpMethod();
    public function getName();
    public function setRoute($route);
    public function setHttpMethod($http_method);
    public function setName($name);

    public function getScope();
    public function isActive();
    public function setStatus($active);

    /**
     * @return IApi
     */
    public function getApi();

} 