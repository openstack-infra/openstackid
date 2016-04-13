<?php namespace OAuth2\Models;
/**
 * Class Principal
 * @package OAuth2\Models
 */
final class Principal implements IPrincipal
{
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var int
     */
    private $auth_time;

    /**
     * OP Browser state
     * @see http://openid.net/specs/openid-connect-session-1_0.html#OPiframe
     * @var string
     */
    private $opbs;

    /**
     * @return int
     */
    public function getAuthTime()
    {
        return (int)$this->auth_time;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * @return string
     */
    public function getOPBrowserState()
    {
        return $this->opbs;
    }

    /**
     * @param array $state
     * @return $this
     */
    public function setState(array $state)
    {
        $this->user_id   = intval($state[0]);
        $this->auth_time = intval($state[1]);
        $this->opbs      = $state[2];
        return $this;
    }
}