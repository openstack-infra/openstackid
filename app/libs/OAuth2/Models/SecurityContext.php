<?php namespace OAuth2\Models;
/**
 * Class SecurityContext
 * @package OAuth2\Models
 */
final class SecurityContext
{
    /**
     * @var int
     */
    private $requested_user_id;

    /**
     * @var bool
     */
    private $requested_auth_time;

    /**
     * @return int
     */
    public function getRequestedUserId()
    {
        return $this->requested_user_id;
    }

    /**
     * @param int $requested_user_id
     * @return $this
     */
    public function setRequestedUserId($requested_user_id)
    {
        $this->requested_user_id = $requested_user_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthTimeRequired()
    {
        return $this->requested_auth_time;
    }

    /**
     * @param bool $requested_auth_time
     * @return $this
     */
    public function setAuthTimeRequired($requested_auth_time)
    {
        $this->requested_auth_time = $requested_auth_time;
        return $this;
    }

    /**
     * @return array
     */
    public function getState()
    {
        return array
        (
            $this->requested_user_id,
            $this->requested_auth_time,
        );
    }

    /**
     * @param array $state
     * @return $this
     */
    public function setState(array $state)
    {
        $this->requested_user_id         = $state[0];
        $this->requested_auth_time       = $state[1];
        return $this;
    }
}