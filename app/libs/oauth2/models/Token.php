<?php

namespace oauth2\models;

use DateInterval;
use DateTime;
use DateTimeZone;
use utils\IPHelper;
use utils\model\Identifier;

/**
 * Class Token
 * Defines the common behavior for all emitted tokens
 * @package oauth2\models
 */
abstract class Token extends Identifier
{

    const DefaultByteLength = 32;

    protected $issued;
    /**
     * oauth2 client id
     * @var string
     */
    protected $client_id;
    protected $len;
    protected $scope;
    protected $audience;
    protected $from_ip;
    protected $is_hashed;
    protected $user_id;

    public function __construct($len = self::DefaultByteLength)
    {
        parent::__construct($len);

        $this->is_hashed = false;
        $this->issued = gmdate("Y-m-d H:i:s", time());
        $this->from_ip = IPHelper::getUserIp();
    }

    public function getIssued()
    {
        return $this->issued;
    }

    public function getScope()
    {
        return $this->scope;
    }

    /**
     * oauth2 client id
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @return string
     */
    public function getFromIp()
    {
        return $this->from_ip;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return intval($this->user_id);
    }

    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if (intval($this->lifetime) == 0) {
            return 0;
        }
        $created_at = new DateTime($this->issued, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at) {
            return -1;
        }
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;

        return $seconds;
    }

    public function isHashed()
    {
        return $this->is_hashed;
    }

    public abstract function toJSON();


    public abstract function fromJSON($json);
} 