<?php
namespace openid\model;

use openid\exceptions\InvalidNonce;
use openid\helpers\OpenIdErrorMessages;
use utils\model\Identifier;

/**
 * Class OpenIdNonce
 * @package openid\model
 */
final class OpenIdNonce extends Identifier
{
    const NonceRegexFormat = '/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z(.*)/';
    const NonceTimeFormat = '%Y-%m-%dT%H:%M:%SZ';

    private $timestamp;
    private $unique_id;


    /**
     * @param int $len
     * @param int $lifetime
     */
    public function __construct($len, $lifetime = 0)
    {
        parent::__construct($len, $lifetime);
    }

    public function getRawFormat()
    {
        return $this->value;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getUniqueId()
    {
        return $this->$unique_id;
    }

    /**
     * The time-stamp MAY be used to reject responses that are too far away from the current time,
     * limiting the amount of time that nonces must be stored to prevent attacks.
     * The acceptable range is out of the scope of this specification.
     * A larger range requires storing more nonces for a longer time.
     * A shorter range increases the chance that clock-skew and transaction time will cause
     * a spurious rejection.
     * @param $allowed_skew
     * @return bool
     */
    public function isValid($allowed_skew)
    {
        $now = time();
        // Time after which we should not use the nonce
        $past = $now - $allowed_skew;
        // Time that is too far in the future for us to allow
        $future = $now + $allowed_skew;

        // the stamp is not too far in the future and is not too far
        // in the past
        return (($past <= $this->timestamp) && ($this->timestamp <= $future));
    }

    /**
     * @param string $value
     * @return $this
     * @throws InvalidNonce
     */
    public function setValue($value)
    {
        // Extract a timestamp from the given nonce string
        $result = preg_match(self::NonceRegexFormat, $value, $matches);
        if ($result != 1 || count($matches) != 8) {
            throw new InvalidNonce(sprintf(OpenIdErrorMessages::InvalidNonceFormatMessage, $value));
        }

        list($unused,
            $tm_year,
            $tm_mon,
            $tm_mday,
            $tm_hour,
            $tm_min,
            $tm_sec,
            $unique_id) = $matches;

        $timestamp = @gmmktime($tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year);

        if ($timestamp == false || $timestamp < 0) {
            throw new InvalidNonce(sprintf(OpenIdErrorMessages::InvalidNonceTimestampMessage, $value));
        }

        $this->timestamp = $timestamp;
        $this->unique_id = $unique_id;
        $this->len       = strlen($value);

        return parent::setValue($value);
    }

    static public function fromValue($value)
    {
        $nonce = new OpenIdNonce(255);

        return $nonce->setValue($value);
    }
} 