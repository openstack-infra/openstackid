<?php

namespace oauth2\models;


interface IResourceServer {

    /**
     * get resource server host
     * @return string
     */
    public function getHost();

    public function setHost($host);

    /**
     * tells if resource server is active or not
     * @return bool
     */
    public function isActive();
    public function setActive($active);

    /**
     * get resource server ip address
     * @return string
     */
    public function getIp();

    public function setIp($ip);

    /**
     * get resource server friendly name
     * @return mixed
     */
    public function getFriendlyName();
    public function setFriendlyName($friendly_name);

    /**
     * @return IClient
     */
    public function getClient();

} 