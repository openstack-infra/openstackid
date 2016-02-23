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

    /**
     * @param bool $active
     * @return void
     */
    public function setActive($active);


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

    /**
     * @param string $ip
     * @return bool
     */
    public function isOwn($ip);

    /**
     * @return string
     */
    public function getIPAddresses();
} 