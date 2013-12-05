<?php

namespace oauth2\models;


interface IApiScope {
    public function getShortDescription();
    public function getName();
    public function getDescription();
    public function isActive();
    public function getApiName();
} 